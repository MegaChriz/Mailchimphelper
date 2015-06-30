<?php

/**
 * @file
 * Contains \Drupal\mailchimphelper\Plugin\Rules\RulesCondition\IsSaving class.
 */

namespace Drupal\mailchimphelper\Plugin\Rules\RulesCondition;

/**
 * Condition plugin for checking if an account is currently being saved.
 */
class IsSaving extends PluginBase {
  /**
   * Defines the condition.
   */
  public static function getInfo() {
    return array(
      'name' => 'mailchimphelper_mail_is_saving',
      'label' => t('Entity with mail address is being saved'),
      'parameter' => array(
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
  public function execute($mail) {
    return drupal_static('mailchimphelper.' . $mail, FALSE);
  }
}
