<?php

namespace Drupal\mailchimphelper\Tests\MailChimp;

/**
 * Virtual MailChimp List.
 */
class MailchimpLists extends \Mailchimp\Tests\MailchimpLists {
  /**
   * A list of subscribers.
   */
  private $members = [];

  /**
   * MailchimpLists object constructor.
   */
  public function __construct($api_key, $api_user = 'apikey', $http_options = []) {
    parent::__construct($api_key, $api_user, $http_options);

    $data = variable_get('mailchimphelper_mailchimplist_class_data', array());
    foreach ($data as $key => $value) {
      $this->$key = $value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addOrUpdateMember($list_id, $email, $parameters = [], $batch = FALSE) {
    $response = parent::addOrUpdateMember($list_id, $email, $parameters, $batch);

    $member = $this->getMemberInfo($list_id, $email);
    if ($member) {
      // Member already exist. Merge data.
      // @todo merge recursively?
      foreach (get_object_vars($response) as $key => $value) {
        $member->$key = $value;
      }
    }
    else {
      $member = $response;
    }

    // Add in default merge fields.
    $merges = $this->getMergeFields($list_id);
    foreach ($merges->merge_fields as $field) {
      if (!isset($member->merge_fields->{$field->tag})) {
        $member->merge_fields->{$field->tag} = NULL;
      }
    }

    // Add to list.
    $this->members[$list_id][$email] = $response;

    // Save data.
    $this->saveClassData();

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function getMemberInfo($list_id, $email, $parameters = []) {
    if (isset($this->members[$list_id][$email])) {
      return $this->members[$list_id][$email];
    }

    return NULL;
  }

  /**
   * Saves class data.
   */
  private function saveClassData() {
    variable_set('mailchimphelper_mailchimplist_class_data', get_object_vars($this));
  }
}
