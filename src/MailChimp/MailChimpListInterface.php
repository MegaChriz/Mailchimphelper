<?php

namespace Drupal\mailchimphelper\MailChimp;

/**
 * Interface for MailChimp list methods.
 */
interface MailChimpListInterface {
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
