<?php

namespace Drupal\mailchimphelper\MailChimp;

/**
 * Class for a MailChimp group.
 */
class MailChimpGroup {
  // ---------------------------------------------------------------------------
  // PROPERTIES
  // ---------------------------------------------------------------------------

  /**
   * The list that this category belongs to.
   *
   * @var Drupal\mailchimphelper\MailChimp\MailChimpGroupCategory
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
   * MailChimpGroupCategory object constructor.
   *
   * @param Drupal\mailchimphelper\MailChimp\MailChimpGroupCategory $category
   *   A GroupCategory instance.
   * @param object $data
   *   The data received via the MailChimp API.
   */
  public function __construct(MailChimpGroupCategory $category, $data) {
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
