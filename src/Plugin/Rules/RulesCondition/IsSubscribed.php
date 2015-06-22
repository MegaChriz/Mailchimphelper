<?php

/**
 * @file
 * Contains \Drupal\mailchimphelper\Plugin\Rules\RulesCondition\IsSubscribed class.
 */

namespace Drupal\mailchimphelper\Plugin\Rules\RulesCondition;

/**
 * Condition plugin for checking if a mail address is subscribed to a list.
 */
class IsSubscribed extends PluginBase {
  /**
   * Defines the condition.
   */
  public static function getInfo() {
    return array(
      'name' => 'mailchimphelper_is_subscribed',
      'label' => t('Email is subscribed'),
      'parameter' => array(
        'list_id' => array(
          'label' => t('Mailchimp list'),
          'type' => 'text',
          'options list' => 'mailchimphelper_get_lists_options',
        ),
        'email' => array(
          'label' => t('E-mail address'),
          'type' => 'text',
        ),
      ),
    ) + static::defaultInfo();
  }

  /**
   * Executes the condition.
   */
  public function execute($list_id, $email) {
    return mailchimp_is_subscribed($list_id, $email, TRUE);
  }
}
