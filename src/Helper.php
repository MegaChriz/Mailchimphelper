<?php

namespace Drupal\mailchimphelper;

use Drupal\mailchimphelper\Mailchimp\List;

/**
 * Main class.
 */
class Helper {

  /**
   * Returns a new list object.
   *
   * @return \Drupal\mailchimphelper\Mailchimp\List
   *   A list instance.
   */
  public function getList($list_id) {
    return List::getInstance($list_id);
  }

}
