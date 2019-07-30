<?php

namespace Drupal\mailchimphelper\Plugin\Field\FieldFormatter;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\mailchimp_signup\Form\MailchimpSignupPageForm;

/**
 * Plugin implementation of the 'mailchimp signup' formatter.
 *
 * @FieldFormatter(
 *   id = "mailchimphelper_signup_formatter",
 *   label = @Translation("Signup form"),
 *   description = @Translation("Display a referenced mailchimp signup form."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class MailchimpSignupFormatter extends EntityReferenceFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $signup) {
      $form = new MailchimpSignupPageForm();

      $form_id = 'mailchimp_signup_subscribe_page_' . $signup->id . '_form';
      $form->setFormID($form_id);
      $form->setSignup($signup);

      $elements[$delta] = \Drupal::formBuilder()->getForm($form);
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return $field_definition->getSetting('handler') === 'default:mailchimp_signup';
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity) {
    return AccessResult::allowed();
  }

}
