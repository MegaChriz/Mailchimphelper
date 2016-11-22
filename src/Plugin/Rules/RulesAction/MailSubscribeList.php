<?php

namespace Drupal\mailchimphelper\Plugin\Rules\RulesAction;

/**
 * Action plugin for subscribing a mail address to a mailchimp list.
 */
class MailSubscribeList extends PluginBase {
  /**
   * Defines the action.
   */
  public static function getInfo() {
    return array(
      'name' => 'mailchimphelper_mail_subscribe_list',
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
      'callbacks' => array(
        'info_alter' => 'mailchimphelper_rules_action_mail_subscribe_list_info_alter'
      ),
    ) + static::defaultInfo();
  }

  /**
   * Executes the action.
   */
  public function execute($list_id) {
    $args = func_get_args();

    $email = NULL;
    $merge_vars = array();

    // Retrieve mergevars info for this list.
    $mergevars_per_list = mailchimp_get_mergevars(array($list_id));

    // Index list variables by id.
    $merge_vars_info = array();
    foreach ($mergevars_per_list[$list_id] as $var) {
      $id = 'mergevars_' . $var->tag;
      $merge_vars_info[$id] = $var;
    }

    // Since we deal with dynamic arguments, we need to find out what each
    // argument means. We do so by examining the parameter info of the
    // Rules action.
    $param_info = $this->element->pluginParameterInfo();
    $arg_count = 0;
    foreach ($param_info as $param_key => $param_value) {
      if (isset($merge_vars_info[$param_key])) {
        // Yay. We found an argument that belongs to the mailchimp's merge variables,
        // set a mergevar variable.
        $merge_vars_key = $merge_vars_info[$param_key]->tag;
        if (strlen($args[$arg_count])) {
          $merge_vars[$merge_vars_key] = $args[$arg_count];
        }

        // One of the mailchimp's merge variables is expected to be a mail address.
        // We can get the mail address by checking the merge variables's field type.
        // @todo Maybe the email parameter should be a "native" parameter instead?
        if (is_null($email) && $merge_vars_info[$param_key]->type == 'email') {
         // We take only the first one.
         $email = $args[$arg_count];
        }
      }
      $arg_count++;
    }

    // Subscribe e-mail!
    mailchimp_subscribe($list_id, $email, $merge_vars, array(), FALSE, 'html');
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
      // In the first step show only the list select.
      foreach (element_children($form['parameter']) as $key) {
        switch ($key) {
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

  /**
   * Options list callback for mergevar select fields.
   */
  public static function mergeVarOptionsList($element, $key) {
    $list_id = $element->settings['list_id'];
    $mergevars_per_list = mailchimp_get_mergevars(array($list_id));
    $mergevars = $mergevars_per_list[$list_id];

    foreach ($mergevars as $var) {
      $id = 'mergevars_' . $var->tag;
      if ($id == $key) {
        return $var->options->choices;
      }
    }

    // Option not found.
    return array();
  }
}
