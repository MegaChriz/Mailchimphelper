<?php

/**
 * @file
 * Contains \Drupal\mailchimphelper\Plugin\Rules\RulesAction\SubscribeInterestGroupsMultiple class.
 */

namespace Drupal\mailchimphelper\Plugin\Rules\RulesAction;

/**
 * Action plugin for subscribing a mail address to interest groups of a mailchimp list.
 */
class SubscribeInterestGroupsMultiple extends PluginBase {
  /**
   * Defines the action.
   */
  public static function getInfo() {
    return array(
      'name' => 'mailchimphelper_lists_subscribe_intgroup_multiple',
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
    ) + static::defaultInfo();
  }

  /**
   * Action callback: Subscribe a mail address to multiple list groups.
   */
  function execute($email, $list_id, $int_groups) {
    // Flatten interest list and only pick enabled ones.
    $interests = array();
    foreach ($int_groups as $category) {
      foreach ($category as $group_id => $enabled) {
        if ($enabled) {
          $interests[$group_id] = TRUE;
        }
      }
    }

    $subscribed = mailchimp_is_subscribed($list_id, $email);
    if (!$subscribed) {
      mailchimp_subscribe($list_id, $email, array(), $interests, FALSE, 'html');
    }
    else {
      mailchimp_update_member($list_id, $email, array(), $interests, 'html');
    }
  }

  /**
   * Form alter callback for rules action to provide extra subscriber data.
   */
  function form_alter(&$form, $form_state, $options) {
    $first_step = empty($this->element->settings['list_id']);

    $form['reload'] = array(
      '#weight' => 5,
      '#type' => 'submit',
      '#name' => 'reload',
      '#value' => $first_step ? t('Continue') : t('Reload form'),
      '#limit_validation_errors' => array(array('parameter', 'list_id')),
      '#submit' => array(array($this, 'rebuildForm')),
      '#ajax' => rules_ui_form_default_ajax(),
    );
    // Use ajax and trigger as the reload button.
    $form['parameter']['list_id']['settings']['list_id']['#ajax'] = $form['reload']['#ajax'] + array(
      'event' => 'change',
      'trigger_as' => array('name' => 'reload'),
    );

    if ($first_step) {
      // In the first step show only the fields for email and list_id.
      foreach (element_children($form['parameter']) as $key) {
        switch ($key) {
          case 'email':
          case 'list_id':
            break;

          default:
            unset($form['parameter'][$key]);
            break;
        }
      }
      unset($form['submit']);
      unset($form['provides']);
      // Disable #ajax for the first step as it has troubles with lazy-loaded JS.
      // @todo: Re-enable once JS lazy-loading is fixed in core.
      unset($form['parameter']['list_id']['settings']['list_id']['#ajax']);
      unset($form['reload']['#ajax']);
    }
    else {
      // Hide the reload button in case js is enabled and it's not the first step.
      $form['reload']['#attributes'] = array('class' => array('rules-hide-js'));
    }
  }
}
