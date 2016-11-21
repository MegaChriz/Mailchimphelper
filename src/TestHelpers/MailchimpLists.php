<?php

namespace Drupal\mailchimphelper\TestHelpers;

use \stdClass;
use Mailchimp\Tests\MailchimpLists as MailchimpListsBase;

/**
 * Virtual MailChimp List.
 */
class MailchimpLists extends MailchimpListsBase {
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
  public function getInterestCategories($list_id, $parameters = []) {
    parent::getInterestCategories($list_id, $parameters);

    $response = (object) [
      'list_id' => $list_id,
      'categories' => [
        (object) [
          'list_id' => $list_id,
          'id' => 'cat1',
          'title' => 'Test Interest Category 1',
        ],
        (object) [
          'list_id' => $list_id,
          'id' => 'cat2',
          'title' => 'Test Interest Category 2',
        ],
      ],
      'total_items' => 2,
    ];

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function getInterests($list_id, $interest_category_id, $parameters = []) {
    parent::getInterests($list_id, $interest_category_id, $parameters);

    $response = new stdClass();

    switch ($interest_category_id) {
      case 'cat1':
        $response = (object) [
          'interests' => [
            (object) [
              'category_id' => $interest_category_id,
              'list_id' => $list_id,
              'id' => 'int1dot1',
              'name' => 'Test Interest 1.1',
            ],
            (object) [
              'category_id' => $interest_category_id,
              'list_id' => $list_id,
              'id' => 'int1dot2',
              'name' => 'Test Interest 1.2',
            ],
          ],
          'total_items' => 2,
        ];
        break;

      case 'cat2':
        $response = (object) [
          'interests' => [
            (object) [
              'category_id' => $interest_category_id,
              'list_id' => $list_id,
              'id' => 'int2dot1',
              'name' => 'Test Interest 2.1',
            ],
            (object) [
              'category_id' => $interest_category_id,
              'list_id' => $list_id,
              'id' => 'int2dot2',
              'name' => 'Test Interest 2.2',
            ],
          ],
          'total_items' => 2,
        ];
        break;
    }

    return $response;
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
    if (!isset($member->merge_fields)) {
      $member->merge_fields = new stdClass();
    }
    foreach ($merges->merge_fields as $field) {
      if (!isset($member->merge_fields->{$field->tag})) {
        $member->merge_fields->{$field->tag} = NULL;
      }
    }

    // Add in default interest groups.
    if (!isset($member->interests)) {
      $member->interests = new stdClass();
    }
    $category_data = $this->getInterestCategories($list_id);
    foreach ($category_data->categories as $category) {
      $interests_data = $this->getInterests($list_id, $category->id);
      foreach ($interests_data->interests as $interest) {
        if (!isset($member->interests->{$interest->id})) {
          $member->interests->{$interest->id} = FALSE;
        }
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
