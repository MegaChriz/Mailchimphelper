<?php

namespace Drupal\mailchimphelper\Mailchimp;

/**
 * Interface for Mailchimp list methods.
 */
interface ListInterface {

  /**
   * Returns ID of list.
   *
   * @return string
   *   The list ID.
   */
  public function getId();

  /**
   * Returns aggregated object.
   *
   * @return object
   */
  public function getList();

}
