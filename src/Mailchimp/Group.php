<?php

namespace Drupal\mailchimphelper\Mailchimp;

/**
 * Class for a Mailchimp group.
 */
class Group {

  // ---------------------------------------------------------------------------
  // PROPERTIES
  // ---------------------------------------------------------------------------

  /**
   * The list that this category belongs to.
   *
   * @var Drupal\mailchimphelper\Mailchimp\GroupCategory
   */
  protected $category;

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
   * GroupCategory object constructor.
   *
   * @param Drupal\mailchimphelper\Mailchimp\GroupCategory $category
   *   A GroupCategory instance.
   * @param object $data
   *   The data received via the Mailchimp API.
   */
  public function __construct(GroupCategory $category, $data) {
    $this->category = $category;
    $this->object = $data;
  }

  // ---------------------------------------------------------------------------
  // GETTERS
  // ---------------------------------------------------------------------------

  /**
   * Returns group ID.
   */
  public function getId() {
    return $this->object->id;
  }

  /**
   * Returns name of group.
   */
  public function getName() {
    return $this->object->name;
  }

}
