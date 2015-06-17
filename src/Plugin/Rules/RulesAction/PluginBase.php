<?php

/**
 * @file
 * Contains \Drupal\mailchimphelper\Plugin\Rules\RulesAction\PluginBase class.
 */

namespace Drupal\mailchimphelper\Plugin\Rules\RulesAction;

use \RulesActionHandlerBase;

/**
 * Base plugin for Mailchimphelper rules actions.
 */
class PluginBase extends RulesActionHandlerBase {
  /**
   * Returns default info for getInfo methods.
   */
  public static function defaultInfo() {
    return array(
      'group' => t('Mailchimp'),
    );
  }

  /**
   * Implements \RulesPluginHandlerInterface::access().
   */
  public function access() {
    return user_access('administer mailchimp');
  }
}