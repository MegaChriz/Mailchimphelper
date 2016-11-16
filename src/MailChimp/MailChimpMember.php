<?php

namespace Drupal\mailchimphelper\MailChimp;

use \stdClass;

/**
 * Class for a MailChimp member.
 */
class MailChimpMember {
  // ---------------------------------------------------------------------------
  // PROPERTIES
  // ---------------------------------------------------------------------------

  /**
   * The list that this category belongs to.
   *
   * @var Drupal\mailchimphelper\MailChimp\MailChimpListInterface
   */
  protected $list;

  /**
   * The aggregated data object.
   *
   * @var object
   */
  protected $object;

  // ---------------------------------------------------------------------------
  // CONSTRUCT
  // ---------------------------------------------------------------------------

  /**
   * MailChimpMember object constructor.
   *
   * @param Drupal\mailchimphelper\MailChimp\MailChimpListInterface $list
   *   A MailChimpListInterface instance.
   * @param object $data
   *   The data received via the MailChimp API.
   */
  public function __construct(MailChimpListInterface $list, $data) {
    $this->list = $list;
    if (is_object($data) && $data != new stdClass()) {
      $this->object = $data;
    }
    $this->groups = array();
  }

  // ---------------------------------------------------------------------------
  // ACTION
  // ---------------------------------------------------------------------------

  /**
   * Checks if object is set.
   *
   * @throws Drupal\mailchimphelper\MailChimp\MailChimpException
   *   In case object is not set.
   */
  protected function requireData() {
    if (!isset($this->object)) {
      throw new MailChimpException('No member info available.');
    }
  }

  // ---------------------------------------------------------------------------
  // GETTERS
  // ---------------------------------------------------------------------------

  /**
   * Returns member ID.
   *
   * @return string
   *   The member ID.
   */
  public function getId() {
    $this->requireData();
    return $this->object->id;
  }

  /**
   * Returns subscribed mail adress.
   *
   * @return string
   *   The member's mail address.
   */
  public function getMailAddress() {
    $this->requireData();
    return $this->object->email_address;
  }

  /**
   * Returns status of subscription.
   *
   * @return string
   *   The subscription status.
   */
  public function getStatus() {
    $this->requireData();
    return $this->object->status;
  }

  /**
   * Returns a merge variable.
   *
   * @param string $varname
   *   The variable to get.
   *
   * @return mixed
   *   The mergevar's value.
   */
  public function getMergeField($varname) {
    $this->requireData();
    return $this->object->merge_fields->$varname;
  }

  /**
   * Returns a flat list of interest groups that the member is subscribed to.
   *
   * @return array
   *   The member's interests.
   */
  public function getGroups() {
    try {
      $this->requireData();
      return $this->object->interests;
    }
    catch (MailChimpException $e) {
      return array();
    }
  }

  /**
   * Returns a list of interest groups, indexed per category.
   */
  public function getGroupsPerCategory() {
    $return = array();
    $interests = $this->getGroups();

    if (!empty($interests)) {
      $groups = $this->list->getAllGroups();
      foreach ($groups as $category_id => $category) {
        foreach ($category->getGroups() as $group_id => $group) {
          $return[$category_id][$group_id] = !empty($interests->{$group_id}) ? $group_id : FALSE;
        }
      }
    }

    return $return;
  }
}
