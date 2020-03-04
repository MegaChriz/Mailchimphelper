<?php

namespace Drupal\mailchimphelper;

use Drupal\mailchimphelper\Mailchimp\MailchimpList;

/**
 * Main class.
 */
class Helper {

  /**
   * Returns a new list object.
   *
   * @return \Drupal\mailchimphelper\Mailchimp\MailchimpList
   *   A list instance.
   */
  public function getList($list_id) {
    return MailchimpList::getInstance($list_id);
  }

}
