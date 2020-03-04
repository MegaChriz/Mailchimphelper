<?php

namespace Drupal\mailchimphelper\Plugin\Field\FieldFormatter;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mailchimphelper\Form\MailchimpSignupPageForm;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Plugin for displaying a mailchimp signup form with tag.
 *
 * @FieldFormatter(
 *   id = "mailchimphelper_tag_signup",
 *   label = @Translation("Mailchimp signup form"),
 *   field_types = {
 *     "string",
 *   }
 * )
 */
class MailchimpSignupWithTagFormatter extends FormatterBase {

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
   * The mailchimp signup entity storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface
   */
  protected $mailchimpSignupStorage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $formatter = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $formatter->setClassResolver($container->get('class_resolver'));
    $formatter->setFormBuilder($container->get('form_builder'));
    $formatter->setMailchimpSignupStorage($container->get('entity_type.manager')->getStorage('mailchimp_signup'));

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
   * Sets the mailchimp signup entity storage.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $mailchimp_signup_storage
   *   The mailchimp signup entity storage.
   */
  public function setMailchimpSignupStorage(ConfigEntityStorageInterface $mailchimp_signup_storage) {
    $this->mailchimpSignupStorage = $mailchimp_signup_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'mailchimp_signup' => NULL,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $signups = $this->mailchimpSignupStorage->loadMultiple();
    $options = [];
    foreach ($signups as $signup) {
      $options[$signup->id()] = $signup->label();
    }

    $element['mailchimp_signup'] = [
      '#title' => $this->t('Mailchimp signup'),
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => $this->getSetting('mailchimp_signup'),
      '#required' => TRUE,
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $signup_id = $this->getSetting('mailchimp_signup');
    $label = $this->t('Please select');
    if ($signup_id) {
      $signup = $this->mailchimpSignupStorage->load($signup_id);
      if ($signup) {
        $label = $signup->label();
      }
    }

    $summary = [];
    $summary[] = t('Signup form: @label', ['@label' => $label]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $content = [];

    $signup_id = $this->getSetting('mailchimp_signup');
    if (!$signup_id) {
      return;
    }

    $signup = $this->mailchimpSignupStorage->load($signup_id);
    if (!$signup) {
      throw new RuntimeException($this->t('Signup form "@name" not found.', [
        '@name' => $signup_id,
      ]));
    }

    $form = $this->classResolver->getInstanceFromDefinition(MailchimpSignupPageForm::class);

    $form_id = 'mailchimp_signup_subscribe_page_' . $signup->id . '_form';
    $form->setFormID($form_id);
    $form->setSignup($signup);

    $tags = [];
    foreach ($items as $delta => $item) {
      $tags[] = $item->value;
    }
    $form->setTags($tags);

    return $this->formBuilder->getForm($form);
  }

}
