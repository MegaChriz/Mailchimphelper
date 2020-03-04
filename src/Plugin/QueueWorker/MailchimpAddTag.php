<?php

namespace Drupal\mailchimphelper\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\mailchimphelper\Helper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A queue worker for tagging mailchimp members.
 *
 * @QueueWorker(
 *   id = "mailchimphelper_tag_member",
 *   title = @Translation("Mailchimp tag member"),
 * )
 */
class MailchimpAddTag extends QueueWorkerBase {

  /**
   * Number of seconds in six hours.
   *
   * @var int
   */
  const SIX_HOURS = 21600;

  /**
   * The mailchimphelper service.
   *
   * @var \Drupal\mailchimphelper\Helper
   */
  protected $mailchimpHelper;

  /**
   * Constructs a new MailchimpAddTag object.
   *
   * @param \Drupal\mailchimphelper\Helper $mailchimphelper
   *   The mailchimphelper service.
   */
  public function __construct(Helper $mailchimphelper) {
    $this->mailchimpHelper = $mailchimphelper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('mailchimphelper')
    );
  }

  /**
   * Queues adding tags to a mailchimp member.
   *
   * @param string $list_id
   *   The mailchimp list ID.
   * @param array $tags
   *   The tags to add to the member.
   * @param string $email
   *   The member's mail address.
   * @param int $retry_until
   *   (optional) The last timestamp on which this item would be requeued.
   *   Defaults to six hours.
   */
  public static function queueItem($list_id, array $tags, $email, $retry_until = NULL) {
    if (is_null($retry_until)) {
      $retry_until = \Drupal::time()->getRequestTime() + static::SIX_HOURS;
    }

    $queue = \Drupal::queue('mailchimphelper_tag_member');
    $queue->createItem([
      'list_id' => $list_id,
      'tags' => $tags,
      'email' => $email,
      'retry_until' => $retry_until,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $this->mailchimpHelper->addTagsMember($data['list_id'], $data['tags'], $data['email'], $data['retry_until']);
  }

}
