<?php

namespace Drupal\mailchimphelper\TestHelpers;

use Drupal\mailchimphelper\MailChimp\MailChimpList;
use Drupal\mailchimphelper\MailChimp\MailChimpMember;

/**
 * Class for generating webhook data.
 */
class Webhook {
  /**
   * Default mail address to use in generating webhook data.
   *
   * @var string
   */
  const DEFAULT_EMAIL = 'mailchimphelper@example.com';

  /**
   * The list to read from.
   *
   * @var \Drupal\mailchimphelper\MailChimp\MailChimpList
   */
  protected $list;

  /**
   * Webhook object constructor.
   *
   * @param \Drupal\mailchimphelper\MailChimp\MailChimpList $list
   *   The list to use.
   */
  public function __construct(MailChimpList $list) {
    $this->list = $list;
  }

  /**
   * Returns webhook data for subscribe.
   *
   * @param string $email
   *   (optional) The mail address of the subscriber that subscribed.
   *
   * @return array
   *   Webhook data.
   */
  public function subscribe($email = NULL) {
    $keys = array(
      'ip_signup' => 'ip_signup',
    );

    return array(
      'type' => 'subscribe',
      'data' => $this->composeMemberData($email, $keys) + array(
        'ip_signup' => '127.0.0.1',
      ),
    );
  }

  /**
   * Returns webhook data for profile change.
   *
   * @param string $email
   *   (optional) The mail address of the subscriber that changed its profile.
   *
   * @return array
   *   Webhook data.
   */
  public function profile($email = NULL) {
    return array(
      'type' => 'profile',
      'data' => $this->composeMemberData($email),
    );
  }

  /**
   * Returns webhook data for mail change.
   *
   * @param string $new_email
   *   (optional) The new mail address of the subscriber.
   * @param string $old_email
   *   (optional) The old mail address of the subscriber.
   *
   * @return array
   *   Webhook data.
   */
  public function upemail($new_email = NULL, $old_email = NULL) {
    return array(
      'type' => 'upemail',
      'data' => array(
        'new_id' => '1dba2ddf46',
        'new_email' => $new_email ? $new_email : 'mailchimphelper_new@example.com',
        'old_email' => $old_email ? $old_email : static::DEFAULT_EMAIL,
        'list_id' => $this->list->getId(),
      ),
    );
  }

  /**
   * Returns webhook data for cleaned.
   *
   * @param string $email
   *   (optional) The mail address that was cleaned.
   *
   * @return array
   *   Webhook data.
   */
  public function cleaned($email = NULL) {
    return array(
      'type' => 'cleaned',
      'data' => array(
        'list_id' => $this->list->getId(),
        'campaign_id' => '4fjk2ma9xd',
        'reason' => 'hard',
        'email' => $email ? $email : static::DEFAULT_EMAIL,
      ),
    );
  }

  /**
   * Returns webhook data for unsubscribe.
   *
   * @param string $email
   *   (optional) The mail address that was cleaned.
   *
   * @return array
   *   Webhook data.
   */
  public function unsubscribe($email = NULL) {
    return array(
      'type' => 'unsubscribe',
       'data' => $this->composeMemberData($email) + array(
         'action' => 'unsub',
         'reason' => 'manual',
       ),
    );
  }

  /**
   * Returns webhook data for delete.
   *
   * @param string $email
   *   (optional) The mail address that was cleaned.
   *
   * @return array
   *   Webhook data.
   */
  public function delete($email = NULL) {
    return array(
      'type' => 'unsubscribe',
       'data' => $this->composeMemberData($email) + array(
         'action' => 'delete',
         'reason' => 'manual',
       ),
    );
  }

  /**
   * Composes member data for webhook data.
   *
   * @param string $email
   *   (optional) The mail address of the subscriber.
   * @param array $keys
   *   (optional) The data to include on the subscriber.
   *
   * @return array
   *   Member data as used in webhook data.
   */
  protected function composeMemberData($email = NULL, $keys = array()) {
    $data = array();

    if ($email) {
      $data['email'] = $email;
      $data['merges']['EMAIL'] = $email;

      $member = $this->list->getMemberInfo($email);
      if ($member->dataExists()) {
        // Basic data.
        $keys += array(
          'id' => 'unique_email_id',
          'email_type' => 'email_type',
          'ip_opt' => 'ip_opt',
        );
        foreach ($keys as $webhook_key => $data_key) {
          if (isset($member->$data_key)) {
            $data[$webhook_key] = $member->$data_key;
          }
        }

        // Merge fields.
        if (isset($member->merge_fields)) {
          $data['merges'] += (array) $member->merge_fields;
        }

        // Interests.
        $data['merges']['INTERESTS'] = $this->generateInterests($member);

        // Interest groups.
        $data['merges']['GROUPINGS'] = $this->generateGroupings($member);
      }
    }

    return $data + array(
      'id' => '1dba2ddf46',
      'email' => static::DEFAULT_EMAIL,
      'email_type' => 'html',
      'ip_opt' => '127.0.0.1',
      'web_id' => '139709481',
      'merges' => $this->defaultMerges(),
      'list_id' => $this->list->getId(),
    );
  }

  /**
   * Generates default data for merge fields.
   *
   * @return array
   *   Default data for merge fields.
   */
  protected function defaultMerges() {
    $data = array(
      'EMAIL' => static::DEFAULT_EMAIL,
    );

    // Merge vars.
    $list_object = $this->list->getList();
    foreach ($list_object->mergevars as $mergevar) {
      $data[$mergevar->tag] = '';
    }

    // Interests.
    $data['INTERESTS'] = '';

    // Interest groups.
    foreach ($this->list->getAllGroups() as $category) {
      $data['GROUPINGS'][] = array(
        'id' => $category->getId(),
        'name' => $category->getName(),
        'groups' => '',
      );
    }

    return $data;
  }

  /**
   * Generates interests data for a member.
   *
   * @param \Drupal\mailchimphelper\MailChimp\MailChimpMember $member
   *   The member to generate interests for.
   *
   * @return string
   *   The interests of the member as a comma separated list.
   */
  protected function generateInterests(MailChimpMember $member) {
    $interests = $member->getGroupsWithTitle();
    return implode(', ', $interests);
  }

  /**
   * Generates groupings data for a member.
   *
   * @param \Drupal\mailchimphelper\MailChimp\MailChimpMember $member
   *   The member to generate interests for.
   *
   * @return array
   *   The groupings data for the member, generated as by a webhook.
   */
  protected function generateGroupings(MailChimpMember $member) {
    $return = array();

    $groupings = $member->getGroupsWithTitlePerCategory();
    foreach ($groupings as $grouping) {
      $grouping['groups'] = implode(', ', $grouping['groups']);
      $return[] = $grouping;
    }

    return $return;
  }
}
