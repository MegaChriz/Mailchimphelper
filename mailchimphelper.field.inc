<?php

/**
 * @file
 * Field hook implementations.
 */

/**
 * Implements hook_field_formatter_info().
 */
function mailchimphelper_field_formatter_info() {
  return [
    'mailchimphelper_signup_formatter' => [
      'label' => t('Signup form'),
      'field types' => [
        'entityreference',
      ],
      'settings' => [
        'tags_field' => '',
      ],
    ],
  ];
}

/**
 * Implements hook_field_formatter_settings_form().
 */
function mailchimphelper_field_formatter_settings_form($field, $instance, $view_mode, $form, &$form_state) {
  $display = $instance['display'][$view_mode];
  $settings = $display['settings'];
  $element = [];

  if ($display['type'] == 'mailchimphelper_signup_formatter') {
    $element['tags_field'] = [
      '#title' => t('Tags field'),
      '#description' => t('Select the field that contains tags to send along.'),
      '#type' => 'textfield',
      '#default_value' => $settings['tags_field'] ?? NULL,
    ];
  }

  return $element;
}

/**
 * Implements hook_field_formatter_settings_summary().
 */
function mailchimphelper_field_formatter_settings_summary($field, $instance, $view_mode) {
  $display = $instance['display'][$view_mode];
  $settings = $display['settings'];
  $summary = [];

  if ($display['type'] == 'mailchimphelper_signup_formatter') {
    if (!empty($settings['tags_field'])) {
      $summary[] = t('Tags taken from: %field', [
        '%field' => $settings['tags_field'],
      ]);
    }
  }

  return implode('<br />', $summary);
}

/**
 * Implements hook_field_formatter_view().
 */
function mailchimphelper_field_formatter_view($entity_type, $entity, $field, $instance, $langcode, $items, $display) {
  $result = [];
  $settings = $display['settings'];

  switch ($display['type']) {
    case 'mailchimphelper_signup_formatter':
      // Get tags from entity, if configured.
      $tags = [];
      if (!empty($settings['tags_field'])) {
        $tags_field = $settings['tags_field'];
        if (isset($entity->{$tags_field}[LANGUAGE_NONE])) {
          foreach ($entity->{$tags_field}[LANGUAGE_NONE] as $key => $value) {
            $tags[] = (string) $value['value'];
          }
        }
      }

      // Display signup form.
      foreach ($items as $delta => $item) {
        $signup = mailchimp_signup_load($item['target_id']);
        if ($signup) {
          $result[$delta] = drupal_get_form('mailchimp_signup_subscribe_block_' . $signup->name . '_form', $signup, 'mailchimp_signup_block', ['tags' => $tags]);
        }
      }
      break;
  }

  return $result;
}
