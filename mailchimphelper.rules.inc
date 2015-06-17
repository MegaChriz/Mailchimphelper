<?php

/**
 * @file
 * Extra rules actions for MailChimp module.
 */

/**
 * Implements hook_rules_data_info().
 */
function mailchimphelper_rules_data_info() {
  return array(
    'mailchimp_interest_groups' => array(
      'label' => t('interest groups'),
      'parent' => 'list',
      'group' => t('MailChimp'),
      'ui class' => 'Drupal\mailchimphelper\Plugin\RulesDataUI\InterestGroups',
    ),
  );
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
