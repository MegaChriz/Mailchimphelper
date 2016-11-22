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
   * Returns if member object is set correctly.
   *
   * @return bool
   *   TRUE if the aggregated object exist.
   *   FALSE otherwise.
   */
  public function dataExists() {
    return isset($this->object);
  }

  /**
   * Magic getter.
   *
   * Returns data from aggregated object.
   *
   * @param string $member
   *   The member to get.
   *
   * @return mixed
   *   The member's value.
   */
  public function __get($member) {
    return $this->object->$member;
  }

  /**
   * Magic isset().
   *
   * Returns data from aggregated object.
   *
   * @param string $member
   *   The member to get.
   *
   * @return bool
   *   If a value exist on the member's data object.
   */
  public function __isset($member) {
    return isset($this->object->$member);
  }

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
   * Returns a flat list of interest groups with ID -> title.
   *
   * @return array
   *   The member's interests, indexed by ID -> title.
   */
  public function getGroupsWithTitle() {
    $return = array();
    $interests = $this->getGroups();

    if (!empty($interests)) {
      $groups = $this->list->getAllGroups();
      foreach ($groups as $category_id => $category) {
        foreach ($category->getGroups() as $group_id => $group) {
          if (!empty($interests->{$group_id})) {
            $return[$group_id] = $group->getName();
          }
        }
      }
    }

    return $return;
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

  /**
   * Returns a list of interest groups with title, indexed per category.
   */
  public function getGroupsWithTitlePerCategory() {
    $return = array();
    $interests = $this->getGroups();

    if (!empty($interests)) {
      $groups = $this->list->getAllGroups();
      foreach ($groups as $category_id => $category) {
        $return[$category_id] = array(
          'id' => $category->getId(),
          'name' => $category->getName(),
          'groups' => array(),
        );

        foreach ($category->getGroups() as $group_id => $group) {
          if (!empty($interests->{$group_id})) {
            $return[$category_id]['groups'][$group_id] = $group->getName();
          }
        }
      }
    }

    return $return;
  }
}
