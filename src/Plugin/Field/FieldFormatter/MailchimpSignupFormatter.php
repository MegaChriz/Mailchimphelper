<?php

namespace Drupal\mailchimphelper\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mailchimphelper\Form\MailchimpSignupPageForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'mailchimp signup' formatter.
 *
 * @FieldFormatter(
 *   id = "mailchimphelper_signup_formatter",
 *   label = @Translation("Signup form"),
 *   description = @Translation("Display a referenced mailchimp signup form."),
 *   field_types = {
 *     "entity_reference",
 *     "mailchimphelper_signup_reference"
 *   }
 * )
 */
class MailchimpSignupFormatter extends EntityReferenceFormatterBase {

  /**
   * The class resolver.
   *
   * @var \Drupal\Core\DependencyInjection\ClassResolverInterface
   */
  protected $classResolver;

  /**
   * The form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $formatter = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $formatter->setClassResolver($container->get('class_resolver'));
    $formatter->setFormBuilder($container->get('form_builder'));
    $formatter->setEntityFieldManager($container->get('entity_field.manager'));

    return $formatter;
  }

  /**
   * Sets the class resolver.
   *
   * @param \Drupal\Core\DependencyInjection\ClassResolverInterface $class_resolver
   *   The class resolver.
   */
  public function setClassResolver(ClassResolverInterface $class_resolver) {
    $this->classResolver = $class_resolver;
  }

  /**
   * Sets the form builder.
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder service.
   */
  public function setFormBuilder(FormBuilderInterface $form_builder) {
    $this->formBuilder = $form_builder;
  }

  /**
   * Sets the entity field manager.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   */
  public function setEntityFieldManager(EntityFieldManagerInterface $entity_field_manager) {
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'tags_field' => NULL,
    ] + parent::defaultSettings();
  }

  /**
   * Returns a list of fields that may be used to reference by.
   *
   * @return array
   *   A list subfields of the entity reference field.
   */
  protected function getPotentialFields() {
    $entity_type_id = $this->fieldDefinition->getTargetEntityTypeId();
    $bundle = $this->fieldDefinition->getTargetBundle();

    // Get fields for this entity type and bundle.
    $field_definitions = $this->entityFieldManager->getFieldDefinitions($entity_type_id, $bundle);
    $options = [
      '' => $this->t('- None -'),
    ];
    foreach ($field_definitions as $id => $definition) {
      $options[$id] = Html::escape($definition->getLabel());
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['tags_field'] = [
      '#title' => $this->t('Tags field'),
      '#description' => $this->t('Select the field that contains tags to send along.'),
      '#type' => 'select',
      '#options' => $this->getPotentialFields(),
      '#default_value' => $this->getSetting('tags_field'),
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $options = $this->getPotentialFields();
    $tags_field = $this->getSetting('tags_field');

    $summary = [];

    if ($tags_field && isset($options[$tags_field])) {
      $summary[] = $this->t('Tags taken from: %field', [
        '%field' => $options[$tags_field],
      ]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $signup) {
      $form = $this->classResolver->getInstanceFromDefinition(MailchimpSignupPageForm::class);

      $form_id = 'mailchimp_signup_subscribe_page_' . $signup->id . '_form';
      $form->setFormID($form_id);
      $form->setSignup($signup);

      // Set tags, if configured.
      $form->setTags($this->getTags($items[$delta]));

      // Build the signup form.
      $elements[$delta] = $this->formBuilder->getForm($form);
    }

    return $elements;
  }

  /**
   * Returns tags to set on the mailchimp signup.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The field item.
   *
   * @return string[]
   *   A list of tags.
   */
  protected function getTags(FieldItemInterface $item) {
    // Check which field to get tags from, if configured.
    $tags_field = $this->getSetting('tags_field');
    if (!$tags_field) {
      // No tags configured. Abort.
      return [];
    }

    // Get entity.
    $entity = $item->getEntity();
    if (!$entity || !$entity->hasField($tags_field)) {
      // The entity does not have the configured field (anymore).
      // Since this formatter doesn't have set a dependency to this field, we
      // need to take into account that this is possible.
      return [];
    }

    // Loop through values for the field.
    $tags = [];
    foreach ($entity->{$tags_field} as $field) {
      $value = $this->fieldGetValue($field);
      if (strlen($value)) {
        $tags[] = (string) $value;
      }
    }

    return $tags;
  }

  /**
   * Returns the value of a field.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The field item whose value to return.
   *
   * @return mixed
   *   The field's value. Null if no value could be returned.
   */
  protected function fieldGetValue($field) {
    if ($field instanceof EntityReferenceItem) {
      // Get referenced entity, can be empty.
      $entity = $field->entity;
      if (!$entity) {
        // No referenced entity found. Abort.
        return;
      }
      return $entity->label();
    }

    $property = $field->mainPropertyName();
    if (!$property) {
      // With no main property it is unknown which value to return. Abort.
      return;
    }
    return $field->{$property};
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
