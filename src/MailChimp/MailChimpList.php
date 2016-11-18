<?php

namespace Drupal\mailchimphelper\MailChimp;

/**
 * Class for MailChimp list methods.
 */
class MailChimpList implements MailChimpListInterface {
  // ---------------------------------------------------------------------------
  // PROPERTIES
  // ---------------------------------------------------------------------------

  /**
   * The ID of the list.
   *
   * @var string
   */
  protected $list_id;

  /**
   * The aggregated list object.
   *
   * @var object
   */
  protected $list;

  /**
   * The groups belonging to this list.
   *
   * @var array
   */
  protected $groups;

  // ---------------------------------------------------------------------------
  // CONSTRUCT
  // ---------------------------------------------------------------------------

  /**
   * MailChimpList object constructor.
   *
   * @param string $list_id
   *   The subscription list ID.
   */
  public function __construct($list_id) {
    $this->list_id = $list_id;
    $this->list = mailchimp_get_list($list_id);
    $this->groups = array();
  }

  // ---------------------------------------------------------------------------
  // GETTERS
  // ---------------------------------------------------------------------------

  /**
   * Magic getter.
   */
  public function __get($member) {
    return $this->list->$member;
  }

  /**
   * Magic isset().
   */
  public function __isset($member) {
    return isset($this->list->$member);
  }

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->list_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getList() {
    return $this->list;
  }

  /**
   * Returns a list of all groups.
   */
  public function getAllGroups($reset = FALSE) {
    if (empty($this->groups) || $reset) {
      $this->groups = array();

      if (empty($this->list->intgroups)) {
        return array();
      }

      foreach ($this->list->intgroups as $category_data) {
        $category = new MailChimpGroupCategory($this, $category_data);
        $category->getGroups();
        $this->groups[$category->getId()] = $category;
      }
    }

    return $this->groups;
  }

  /**
   * Returns a specific group category, if it exists.
   *
   * @param string $category_id
   *   The ID of the group category to get.
   *
   * @return \Drupal\mailchimphelper\MailChimp\MailChimpGroupCategory
   *   An instance of MailChimpGroupCategory.
   *
   * @throws Drupal\mailchimphelper\MailChimp\MailChimpException
   *   In case the group category does not exist.
   */
  public function getGroupCategory($category_id) {
    $groups = $this->getAllGroups();

    if (!isset($groups[$category_id])) {
      throw new MailChimpException(strtr('Group category @category_id does not exist.', array(
        '@category_id' => $category_id,
      )));
    }

    return $groups[$category_id];
  }

  /**
   * Gets the MailChimp member info for a given email address and list.
   *
   * @param string $email
   *   The MailChimp user email address to load member info for.
   * @param bool $reset
   *   Set to TRUE if member info should not be loaded from cache.
   *
   * @return object
   *   Member info object, empty if there is no valid info.
   */
  public function getMemberInfo($email, $reset = FALSE) {
    $memberinfo = mailchimp_get_memberinfo($this->list_id, $email, $reset);
    return new MailChimpMember($this, $memberinfo);
  }

  /**
   * Generates form for interest groups.
   *
   * @param array $defaults
   *   (optional) The default values for the field.
   * @param string $email
   *   (optional) The mail address to subscribe.
   * @param array $options
   *   (optional) Options to set:
   *   - include_hidden: if TRUE, fields for hidden groups are also displayed.
   *     Defaults to FALSE.
   *
   * @return array
   *   A renderable form array.
   */
  public function getInterestGroupsFormField($defaults, $email = NULL, array $options = array()) {
    if (!is_array($defaults)) {
      $defaults = unserialize($defaults);
    }
    $return = array();

    // Option defaults.
    $options += array(
      'include_hidden' => FALSE,
    );

    if (!empty($email)) {
      $interests = $this->getMemberInfo($email)->getGroups();
    }

    foreach ($this->getAllGroups() as $category_id => $category) {
      if (!$options['include_hidden'] && $category->isHidden()) {
        continue;
      }

      if (!empty($interests)) {
        $group_defaults = array();
        foreach ($category->getGroups() as $group) {
          $group_id = $group->getId();
          $group_defaults[$group_id] = !empty($interests->{$group_id}) ? $group_id : 0;
        }
      }
      else {
        $group_defaults = isset($defaults[$category_id]) ? $defaults[$category_id] : array();
      }

      $return[$category_id] = $category->getFormField($group_defaults);
    }

    return $return;
  }
}
