<?php

namespace Drupal\mailchimphelper\MailChimp;

/**
 * Class for a MailChimp merge var.
 */
class MailChimpMergeVar {
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
   * MailChimpMergeVar object constructor.
   *
   * @param Drupal\mailchimphelper\MailChimp\MailChimpListInterface $list
   *   A MailChimpListInterface instance.
   * @param object $data
   *   The data received via the MailChimp API.
   */
  public function __construct(MailChimpListInterface $list, $data) {
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
