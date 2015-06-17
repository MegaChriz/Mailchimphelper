<?php

/**
 * @file
 * Extra rules actions for MailChimp module.
 */

/**
 * Implements hook_rules_action_info().
 */
function mailchimphelper_rules_action_info() {
  $items['mailchimphelper_mail_subscribe_list'] = array(
    'label' => t('Subscribe mail address to a mailchimp list'),
    'parameter' => array(
      'list_id' => array(
        'type' => 'text',
        'label' => t('Mailchimp list'),
        'options list' => 'mailchimphelper_get_lists_options',
        'default mode' => 'input',
      ),
      // Further needed parameters depends on list.
    ),
    'group' => t('Mailchimp'),
    'access callback' => 'mailchimp_lists_rules_access_callback',
    'base' => 'mailchimphelper_rules_action_mail_subscribe_list',
    'callbacks' => array(
      'form_alter' => 'mailchimphelper_rules_action_mail_subscribe_list_form_alter'
    ),
  );
  return $items;
}

/**
 * Action callback: Subscribe a mail address to a list.
 */
function mailchimphelper_rules_action_mail_subscribe_list($list_id) {
  $args = func_get_args();

  $email = NULL;
  $merge_vars = array();

  // Retrieve mergevars info for this list.
  $mergevars_per_list = mailchimp_get_mergevars(array($list_id));
  $mergevars = $mergevars_per_list[$list_id]['merge_vars'];

  // Index list variables by id.
  $merge_vars_info = array();
  foreach ($mergevars as $var) {
    $id = 'mergevars_' . $var['id'];
    $merge_vars_info[$id] = $var;
  }

  // Since we deal with dynamic arguments, we need to find out what each
  // argument means. We do so by examining the parameter info of the
  // Rules action.
  foreach ($args as $arg) {
    if ($arg instanceof RulesAction) {
      // Get the parameter info of this action, so we can get to know what the other arguments are about.
      $param_info = $arg->pluginParameterInfo();
      $arg_count = 0;
      foreach ($param_info as $param_key => $param_value) {
        if (isset($merge_vars_info[$param_key])) {
          // Yay. We found an argument that belongs to the mailchimp's merge variables,
          // set a mergevar variable.
          $merge_vars_key = $merge_vars_info[$param_key]['tag'];
          $merge_vars[$merge_vars_key] = $args[$arg_count];

          // One of the mailchimp's merge variables is expected to be a mail address.
          // We can get the mail address by checking the merge variables's field type.
          // @todo Maybe the email parameter should be a "native" parameter instead?
          if (is_null($email) && $merge_vars_info[$param_key]['field_type'] == 'email') {
           // We take only the first one.
           $email = $args[$arg_count];
          }
        }
        $arg_count++;
      }
    }
  }

  // Subscribe e-mail!
  mailchimp_subscribe($list_id, $email, $merge_vars, FALSE);
}

/**
 * Access callback for the rules integration.
 */
function mailchimphelper_rules_access_callback() {
  return user_access('administer mailchimp');
}

/**
 * Info alteration callback for mailchimphelper_mail_subscribe_list action.
 */
function mailchimphelper_rules_action_mail_subscribe_list_info_alter(&$element_info, RulesAbstractPlugin $element) {
  if (!empty($element->settings['list_id'])) {
    $list_id = $element->settings['list_id'];
    $cache = rules_get_cache();

    // Retrieve mergevars info for this list.
    $mergevars_per_list = mailchimp_get_mergevars(array($list_id));
    $mergevars = $mergevars_per_list[$list_id]['merge_vars'];

    // Create parameters for each variable.
    foreach ($mergevars as $var) {
      $id = 'mergevars_' . $var['id'];
      $param = array(
        'type' => 'text',
        'label' => check_plain($var['name']),
        'default mode' => 'selector',
      );
      if (isset($var['choices'])) {
        $param['options list'] = 'mailchimphelper_rules_action_mail_subscribe_list_options';
      }
      if (empty($var['req'])) {
        $param['optional'] = TRUE;
      }
      $element_info['parameter'][$id] = $param;
    }
  }
}

/**
 * Options list callback for select fields of the mailchimphelper_rules_action_mail_subscribe_list action.
 */
function mailchimphelper_rules_action_mail_subscribe_list_options($element, $key) {
  $list_id = $element->settings['list_id'];
  $mergevars_per_list = mailchimp_get_mergevars(array($list_id));
  $mergevars = $mergevars_per_list[$list_id]['merge_vars'];

  foreach ($mergevars as $var) {
    $id = 'mergevars_' . $var['id'];
    if ($id == $key) {
      return $var['choices'];
    }
  }

  // Option not found.
  return array();
}
