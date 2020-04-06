<?php

namespace Drupal\mailchimphelper;

use Drupal\mailchimphelper\MailChimp\MailchimpList;
use Drupal\mailchimphelper\Plugin\QueueWorker\MailchimpAddTag;
use DrupalQueue;
use Exception;
use Mailchimp\MailchimpAPIException;

/**
 * Main class.
 */
class Helper {

  /**
   * Number of seconds to run tasks from the "mailchimphelper_tag_member" queue.
   *
   * @var int
   */
  const TAG_MEMBER_TIME = 10;

  /**
   * Returns a new list object.
   *
   * @return \Drupal\mailchimphelper\Mailchimp\MailchimpList
   *   A list instance.
   */
  public function getList($list_id) {
    return MailchimpList::getInstance($list_id);
  }

  /**
   * Processes items from the queue 'mailchimphelper_tag_member'.
   *
   * Core's queue runner isn't used for that queue, because we don't want any
   * requeued items to be processed during the same cron run. When an item cannot
   * be processed right now, we want to retry it a few minutes later, so a
   * requeued item should at least be postponed to the next cron run.
   */
  public function processTagsMemberQueue() {
    // Perform tasks from "mailchimphelper_tag_member" queue.
    $queue_worker = new MailchimpAddTag($this);
    $queue_name = 'mailchimphelper_tag_member';

    $end = time() + static::TAG_MEMBER_TIME;

    // Make sure that queue class is overridden for this queue.
    $queue_class = variable_get('queue_class_' . $queue_name);
    if (!$queue_class || $queue_class == 'SystemQueue') {
      variable_set('queue_class_' . $queue_name, Queue::class);
    }
    $queue = DrupalQueue::get($queue_name);
    while (time() < $end && ($item = $queue->claimItem())) {
      try {
        if ($item->created > REQUEST_TIME) {
          // Item was postponed! Release the item and continue to the next one.
          $queue->releaseItem($item);
          continue;
        }

        $queue_worker->processItem($item->data);
        $queue->deleteItem($item);
      }
      catch (Exception $e) {
        // In case of exception log it and leave the item in the queue
        // to be processed again later.
        watchdog_exception('cron', $e);
      }
    }
  }

  /**
   * Adds tags to a member only if the member is currently subscribed.
   *
   * @param string $list_id
   *   The ID of the list.
   * @param string[] $tags
   *   A list of tags to add.
   * @param array $email
   *   The email address to add the tag to.
   * @param int $retry_until
   *   (optional) The last timestamp on which this item would be requeued.
   *   Defaults to six hours.
   *
   * @return bool
   *   True if adding the tags was successful.
   *   False if adding tags failed or if the member is not subscribed.
   */
  public function addTagsMember($list_id, array $tags, $email, $retry_until = NULL) {
    if (is_null($retry_until)) {
      $retry_until = REQUEST_TIME + MailchimpAddTag::SIX_HOURS;
    }

    $mc_lists = mailchimp_get_api_object('MailchimpLists');

    // Check if the member's subscription has been confirmed.
    try {
      $member = $mc_lists->getMemberInfo($list_id, $email);
    }
    catch (MailchimpAPIException $e) {
      if ($e->getCode() !== 404) {
        // 404 indicates the email address is not subscribed to this list
        // and can be safely ignored. Surface all other exceptions.
        watchdog_exception('mailchimp', $e);
      }
    }

    try {
      if (isset($member) && $member->status == 'subscribed') {
        // Subscription is confirmed. Add tag.
        $mc_lists->addTagsMember($list_id, $tags, $email);

        // Assume success.
        return TRUE;
      }
    }
    catch (MailchimpAPIException $e) {
      // Log exceptions.
      watchdog_exception('mailchimp', $e);
    }

    // Adding tags failed. Queue this task instead.
    if ($retry_until > REQUEST_TIME) {
      // Not confirmed yet. (Re)queue.
      MailchimpAddTag::queueItem($list_id, $tags, $email, $retry_until);
    }

    return FALSE;
  }

}
