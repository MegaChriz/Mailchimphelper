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
 * Implements hook_rules_condition_info().
 */
function mailchimphelper_rules_condition_info() {
  return array(
    'mailchimphelper_is_subscribed' => array(
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
      'group' => t('MailChimp'),
    ),
  );
}

/**
 * Implements hook_rules_action_info().
 */
function mailchimphelper_rules_action_info() {
  $items['mailchimphelper_lists_subscribe_intgroup_multiple'] = array(
    'label' => t('Subscribe email to multiple mailchimp groups'),
    'parameter' => array(
      'email' => array(
        'type' => 'text',
        'label' => t('E-mail address'),
        'description' => t('The email address to subscribe to a list.'),
      ),
      'list_id' => array(
        'type' => 'text',
        'label' => t('Mailchimp list'),
        'options list' => 'mailchimphelper_get_lists_options',
        'default mode' => 'input',
      ),
      'groups' => array(
        'type' => 'mailchimp_interest_groups',
        'label' => t('Groups'),
        'description' => t('Groups will only be added, not removed.'),
        'optional' => TRUE,
      ),
    ),
    'group' => t('Mailchimp'),
    'access callback' => 'mailchimp_lists_rules_access_callback',
    'base' => 'mailchimphelper_lists_subscribe_intgroup_multiple',
    'callbacks' => array(
      'form_alter' => 'mailchimphelper_rules_action_subscribe_intgroup_multiple_form_alter'
    ),
  );

  return $items;
}

/**
 * Condition callback: check if a mail address is subscribed to a list.
 */
function mailchimphelper_is_subscribed($list_id, $email) {
  return mailchimp_is_subscribed($list_id, $email);
}

/**
 * Action callback: Subscribe a mail address to multiple list groups.
 */
function mailchimphelper_lists_subscribe_intgroup_multiple($email, $list_id, $groups) {
  $mergevars = array('EMAIL' => $email);
  $mergevars['GROUPINGS'] = array();

  // Groups will only be added, not removed. Filter out unchecked groups.
  foreach ($groups as $group_id => $subgroups) {
    $mergevar_grouping = array(
      'id' => $group_id,
      'groups' => array(),
    );
    foreach ($subgroups as $group_name => $enabled) {
      if ($enabled) {
        $mergevar_grouping['groups'][$group_name] = $group_name;
      }
    }
    if (count($mergevar_grouping['groups'])) {
      $mergevars['GROUPINGS'][] = $mergevar_grouping;
    }
  }

  $subscribed = mailchimp_is_subscribed($list_id, $email);
  if (!$subscribed) {
    mailchimp_subscribe($list_id, $email, $mergevars, FALSE, FALSE, 'html', TRUE, FALSE);
  }
  else {
    mailchimp_update_member($list_id, $email, $mergevars, 'html', FALSE);
  }
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
