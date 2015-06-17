<?php

/**
 * @file
 * Contains \Drupal\mailchimphelper\Plugin\RulesDataUI\InterestGroups class.
 */

namespace Drupal\mailchimphelper\Plugin\RulesDataUI;

use \RulesDataUIListText;
use \RulesPlugin;

/**
 *
 */
class InterestGroups extends RulesDataUIListText {
  /**
   *
   */
  public static function inputForm($name, $info, $settings, RulesPlugin $element) {
    if (empty($settings['list_id'])) {
      return array(
        '#markup' => t('Unable to load interest groups because mailchimp list !param_name is unknown.', array(
          '!param_name' => '<code>list_id</code>',
        )),
      );
    }
    $list_id = $settings['list_id'];

    $settings += array($name => isset($info['default value']) ? $info['default value'] : array());
    $form = parent::inputForm($name, $info, $settings, $element);

    $mc_list = mailchimp_get_list($list_id);
    $form[$name] = mailchimp_interest_groups_form_elements($mc_list, $settings[$name]);

    return $form;
  }
}
