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
   * Additional merge vars.
   */
  private $merges = [];

  /**
   * MailchimpLists object constructor.
   */
  public function __construct($api_key, $api_user = 'apikey', $http_options = []) {
    parent::__construct($api_key, $api_user, $http_options);
    $this->init();
  }

  /**
   * Initializes member data from variable.
   */
  public function init() {
    global $conf;
    $conf = variable_initialize();
    $data = variable_get('mailchimphelper_mailchimplist_class_data', []);
    foreach ($data as $key => $value) {
      $this->$key = $value;
    }
  }

  /**
   * Adds an extra merge field to the list.
   *
   * @param int $merge_id
   *   ID of the merge field.
   * @param string $tag
   *   Tag name of the merge field.
   */
  public function addMergeField($merge_id, $tag) {
    $this->merges[$merge_id] = $tag;
    $this->saveClassData();
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
  public function getMergeFields($list_id, $parameters = []) {
    $response = parent::getMergeFields($list_id, $parameters);

    if (count($this->merges)) {
      foreach ($this->merges as $merge_id => $tag) {
        $response->merge_fields[] = (object) [
          'merge_id' => $merge_id,
          'tag' => $tag,
          'list_id' => $list_id,
        ];
      }
      $response->total_items += count($this->merges);
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function removeMember($list_id, $email) {
    parent::removeMember($list_id, $email);

    unset($this->members[$list_id][$email]);

    // Save data.
    $this->saveClassData();
  }

  /**
   * {@inheritdoc}
   */
  public function updateMember($list_id, $email, $parameters = [], $batch = FALSE) {
    $response = parent::updateMember($list_id, $email, $parameters, $batch);

    // Merge with existing member data (which is expected to exist).
    $member = $this->getMemberInfo($list_id, $email);
    $this->mergeMemberInfo($member, $response);

    // Save data.
    $this->saveClassData();

    return $member;
  }

  /**
   * {@inheritdoc}
   */
  public function addOrUpdateMember($list_id, $email, $parameters = [], $batch = FALSE) {
    $response = parent::addOrUpdateMember($list_id, $email, $parameters, $batch);

    $member = $this->getMemberInfo($list_id, $email);
    if ($member) {
      // Member already exist. Merge data.
      $this->mergeMemberInfo($member, $response);
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
    $this->members[$list_id][$email] = $member;

    // Save data.
    $this->saveClassData();

    return $member;
  }

  /**
   * Merges data into existing member.
   *
   * @param object $member
   *   The original member data.
   * @param object $data
   *   The new data.
   */
  protected function mergeMemberInfo($member, $data) {
    // Merge recursively.
    foreach (get_object_vars($data) as $key => $value) {
      if (is_object($value) && isset($member->{$key})) {
        $this->mergeMemberInfo($member->{$key}, $value);
      }
      else {
        $member->{$key} = $value;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getMemberInfo($list_id, $email, $parameters = []) {
    // Make sure that our info is up to date.
    $this->init();

    // Try to find the member.
    if (isset($this->members[$list_id][$email])) {
      return $this->members[$list_id][$email];
    }

    return NULL;
  }

  /**
   * Saves class data.
   */
  private function saveClassData() {
    variable_set('mailchimphelper_mailchimplist_class_data', [
      'members' => $this->members,
      'merges' => $this->merges,
    ]);
  }
}
