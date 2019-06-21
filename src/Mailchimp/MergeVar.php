<?php

namespace Drupal\mailchimphelper\Mailchimp;

/**
 * Class for a Mailchimp merge var.
 */
class MergeVar {

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

  // ---------------------------------------------------------------------------
  // CONSTRUCT
  // ---------------------------------------------------------------------------

  /**
   * MergeVar object constructor.
   *
   * @param Drupal\mailchimphelper\Mailchimp\ListInterface $list
   *   A ListInterface instance.
   * @param object $data
   *   The data received via the Mailchimp API.
   */
  public function __construct(ListInterface $list, $data) {
    $this->list = $list;
    $this->object = $data;
  }

  // ---------------------------------------------------------------------------
  // GETTERS
  // ---------------------------------------------------------------------------

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
   * Returns ID of merge variable.
   */
  public function getId() {
    if (isset($this->object->merge_id)) {
      return $this->object->merge_id;
    }
    return $this->object->tag;
  }

  /**
   * Returns name of merge variable.
   */
  public function getName() {
    if (isset($this->object->name)) {
      return $this->object->name;
    }
    return $this->object->tag;
  }

  /**
   * Returns tag name of merge variable.
   */
  public function getTagName() {
    return $this->object->tag;
  }

  /**
   * Returns if the variable is required.
   *
   * @return bool
   *   TRUE if required.
   *   FALSE otherwise.
   */
  public function isRequired() {
    if (isset($this->object->required)) {
      return $this->object->required;
    }
    return FALSE;
  }

  /**
   * Returns if the variable is hidden by default.
   *
   * @return bool
   *   TRUE if hidden.
   *   FALSE otherwise.
   */
  public function isHidden() {
    if (isset($this->object->public)) {
      return $this->object->public;
    }
    return FALSE;
  }

}
