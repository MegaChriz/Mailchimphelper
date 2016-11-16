<?php

namespace Drupal\mailchimphelper\Plugin\RulesDataUI;

use \RulesDataDirectInputFormInterface;
use \RulesDataUI;
use \RulesPlugin;
use Drupal\mailchimphelper\MailChimp\MailChimpList;

/**
 * Class for setting interest groups within Rules.
 */
class InterestGroups extends RulesDataUI implements RulesDataDirectInputFormInterface {
  /**
   * {@inheritdoc}
   */
  public static function getDefaultMode() {
    return 'input';
  }

  /**
   * {@inheritdoc}
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
    $list = new MailChimpList($element->settings['list_id']);
    $mc_list = mailchimp_get_list($list_id);
    $form[$name] = $list->getInterestGroupsFormField($settings[$name], NULL, array(
      'include_hidden' => TRUE,
    ));

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public static function render($values, $name = NULL, $info = NULL, RulesPlugin $element = NULL) {
    $data = array();

    if (isset($element->settings['list_id'])) {
      $list = new MailChimpList($element->settings['list_id']);
      foreach ($values as $category_id => $category_values) {
        $category = $list->getGroupCategory($category_id);
        foreach ($category_values as $group_id => $enabled) {
          if ($enabled) {
            $data[$group_id] = $category->getGroup($group_id)->getName();
          }
        }
      }
    }
    else {
      // Filter out empty values.
      foreach ($values as $category_id => $category_values) {
        $data += array_filter($category_values, function($value) {
          return !empty($value);
        });
      }
    }

    return array(
      'content' => array('#markup' => check_plain(implode(', ', $data))),
      '#attributes' => array('class' => array('rules-parameter-list')),
    );
  }
}
