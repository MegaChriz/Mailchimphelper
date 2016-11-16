<?php

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
      'group' => t('MailChimp'),
    );
  }

  /**
   * Implements \RulesPluginHandlerInterface::access().
   */
  public function access() {
    return user_access('administer mailchimp');
  }

  /**
   * FAPI submit callback for reloading the form.
   */
  public function rebuildForm($form, &$form_state) {
    rules_form_submit_rebuild($form, $form_state);
    // Clear the parameter modes for the parameters, so they get the proper
    // default values based upon the data types on rebuild.
    $form_state['parameter_mode'] = array();
  }
}