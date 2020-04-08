<?php

namespace Drupal\mailchimphelper\Mailchimp;

/**
 * Class for a Mailchimp group category.
 */
class GroupCategory {

  use CacheTrait;

  // ---------------------------------------------------------------------------
  // PROPERTIES
  // ---------------------------------------------------------------------------

  /**
   * The list that this category belongs to.
   *
   * @var Drupal\mailchimphelper\Mailchimp\ListInterface
   */
  protected $list;

  /**
   * The aggregated data object.
   *
   * @var object
   */
  protected $object;

  /**
   * The groups belonging to this category.
   *
   * @var array
   */
  protected $groups;

  // ---------------------------------------------------------------------------
  // CONSTRUCT
  // ---------------------------------------------------------------------------

  /**
   * GroupCategory object constructor.
   *
   * @param Drupal\mailchimphelper\Mailchimp\ListInterface $list
   *   A ListInterface instance.
   * @param object $data
   *   The data received via the Mailchimp API.
   */
  public function __construct(ListInterface $list, $data) {
    $this->list = $list;
    $this->object = $data;
    $this->groups = [];
  }

  // ---------------------------------------------------------------------------
  // GETTERS
  // ---------------------------------------------------------------------------

  /**
   * Returns category ID.
   */
  public function getId() {
    return $this->object->id;
  }

  /**
   * Returns name of category.
   */
  public function getName() {
    return $this->object->title;
  }

  /**
   * Returns field type of category.
   */
  public function getType() {
    if (!isset($this->object->type)) {
      return 'checkboxes';
    }
    return $this->object->type;
  }

  /**
   * Returns whether or not this group is configured as 'hidden'.
   */
  public function isHidden() {
    return ($this->getType() == 'hidden');
  }

  /**
   * Returns a specific group, if it exists.
   *
   * @param string $group_id
   *   The ID of the group to get.
   *
   * @return \Drupal\mailchimphelper\Mailchimp\Group
   *   An instance of Group.
   *
   * @throws Drupal\mailchimphelper\Mailchimp\Exception
   *   In case the group does not exist.
   */
  public function getGroup($group_id) {
    $groups = $this->getGroups();

    if (!isset($groups[$group_id])) {
      throw new MailchimpException(strtr('Group @group_id does not exist.', [
        '@group_id' => $group_id,
      ]));
    }

    return $groups[$group_id];
  }

  /**
   * Returns a list of groups for this category.
   *
   * @param bool $reset
   *   Whether or not to force getting the list via the Mailchimp API.
   */
  public function getGroups($reset = FALSE) {
    if (empty($this->groups) || $reset) {
      $this->groups = [];

      $list_id = $this->list->getId();
      $category_id = $this->getId();
      $cid = 'list-' . $list_id . '-interests';

      // Try to retrieve interest groups from cache.
      $cache = $reset ? NULL : $this->cacheGet($cid);
      $interests_per_category = !empty($cache) ? $cache : [];

      if (!isset($interests_per_category[$category_id])) {
        $mc_lists = mailchimp_get_api_object('MailchimpLists');
        $interest_data = $mc_lists->getInterests($list_id, $category_id, ['count' => 500]);

        if ($interest_data->total_items < 1) {
          $interests_per_category[$category_id] = [];
          $this->cacheSet($cid, $interests_per_category);
          return [];
        }

        $interests_per_category[$category_id] = $interest_data->interests;
        $this->cacheSet($cid, $interests_per_category);
      }

      foreach ($interests_per_category[$category_id] as $group_data) {
        $group = new Group($this, $group_data);
        $this->groups[$group->getId()] = $group;
      }
    }

    return $this->groups;
  }

  /**
   * Returns a list of group ID => group name.
   *
   * @return array
   *   A list of groups.
   */
  public function getGroupsAsOptions() {
    $return = [];

    foreach ($this->getGroups() as $group) {
      $return[$group->getId()] = $group->getName();
    }

    return $return;
  }

  /**
   * Returns editable form field for this category.
   *
   * @param array $defaults
   *   (optional) The default values for the field.
   *
   * @return array
   *   A renderable form array.
   */
  public function getFormField($defaults = []) {
    if (!empty($email)) {
      $memberinfo = mailchimp_get_memberinfo($list->id, $email);
    }

    // Set the form field type.
    switch ($this->getType()) {
      case 'radio':
        $field_type = 'radios';
        break;

      case 'dropdown':
        $field_type = 'select';
        break;

      case 'hidden':
        $field_type = 'checkboxes';
        break;

      default:
        $field_type = $this->getType();
    }

    // Extract the field options:
    $options = [];
    if ($field_type == 'select') {
      $options[''] = '-- select --';
    }

    $default_values = [];

    // Set interest options and default values.
    foreach ($this->getGroups() as $group) {
      $options[$group->getId()] = $group->getName();
    }

    $name = $this->getName();
    if ($this->isHidden()) {
      $name = t('@name (hidden)', [
        '@name' => $name,
      ]);
    }

    return [
      '#type' => $field_type,
      '#title' => $name,
      '#options' => $options,
      '#default_value' => $defaults,
      '#attributes' => [
        'class' => [
          'mailchimp-newsletter-interests-' . $this->list->getId(),
        ],
      ],
    ];
  }

}
