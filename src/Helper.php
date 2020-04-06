<?php

namespace Drupal\mailchimphelper;

use Drupal\mailchimphelper\Mailchimp\MailchimpList;
use Drupal\mailchimphelper\Plugin\QueueWorker\MailchimpAddTag;
use Mailchimp\MailchimpAPIException;

/**
 * Main class.
 */
class Helper {

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
      $retry_until = \Drupal::time()->getRequestTime() + MailchimpAddTag::SIX_HOURS;
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
    if ($retry_until > \Drupal::time()->getRequestTime()) {
      // Not confirmed yet. (Re)queue.
      MailchimpAddTag::queueItem($list_id, $tags, $email, $retry_until);
    }

    return FALSE;
  }

}
