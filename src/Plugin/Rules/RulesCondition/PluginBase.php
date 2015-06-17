<?php

/**
 * @file
 * Contains \Drupal\mailchimphelper\Plugin\Rules\RulesCondition\PluginBase class.
 */

namespace Drupal\mailchimphelper\Plugin\Rules\RulesCondition;

use \RulesConditionHandlerBase;

/**
 * Base plugin for Mailchimphelper rules actions.
 */
class PluginBase extends RulesConditionHandlerBase {
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