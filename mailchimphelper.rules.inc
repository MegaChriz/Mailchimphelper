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
      'group' => t('MailChimp'),
      'parent' => 'list',
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
    $mergevars = $mergevars_per_list[$list_id];
    if (empty($mergevars)) {
      return;
    }

    // Create parameters for each variable.
    foreach ($mergevars as $var) {
      $id = 'mergevars_' . $var->tag;
      $param = array(
        'type' => 'text',
        'label' => isset($var->name) ? check_plain($var->name) : $var->tag,
        'default mode' => 'selector',
      );
      if (isset($var->options->choices)) {
        $param['options list'] = 'Drupal\mailchimphelper\Plugin\Rules\RulesAction\MailSubscribeList::mergeVarOptionsList';
      }
      if (empty($var->required)) {
        $param['optional'] = TRUE;
      }
      $element_info['parameter'][$id] = $param;
    }
  }
}
