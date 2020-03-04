<?php

namespace Drupal\mailchimphelper\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\mailchimphelper\Helper;
use Drupal\mailchimp_signup\Form\MailchimpSignupPageForm as MailchimpSignupPageFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Subscribe to a Mailchimp list.
 */
class MailchimpSignupPageForm extends MailchimpSignupPageFormBase {

  /**
   * The mailchimphelper service.
   *
   * @var \Drupal\mailchimphelper\Helper
   */
  protected $mailchimpHelper;

  /**
   * Constructs a new MailchimpSignupPageForm object.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\mailchimphelper\Helper $mailchimphelper
   *   The mailchimphelper service.
   */
  public function __construct(MessengerInterface $messenger, Helper $mailchimphelper) {
    parent::__construct($messenger);
    $this->mailchimpHelper = $mailchimphelper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('mailchimphelper')
    );
  }

  /**
   * The tags to send to Mailchimp for this signup.
   *
   * @var array
   */
  protected $tags = [];

  /**
   * The url to redirect to after form submit.
   *
   * @var string
   */
  protected $url;

  /**
   * Returns the tags for the Mailchimp subscription.
   *
   * @return array
   *   The tags that are set.
   */
  public function getTags() {
    return $this->tags;
  }

  /**
   * Sets the tags to send with the Mailchimp subscription.
   *
   * @param array $tags
   *   The tags to send.
   */
  public function setTags(array $tags) {
    $this->tags = $tags;
  }

  /**
   * Sets the url to redirect to.
   *
   * @param string $url
   *   The url to redirect to after form submit.
   */
  public function setRedirectUrl($url) {
    $this->url = $url;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Subscribe.
    parent::submitForm($form, $form_state);

    if (empty($this->tags)) {
      // No tags configured. Abort.
      return;
    }

    $lists = mailchimp_get_lists($this->getSignup()->mc_lists);
    $email = $form_state->getValue(['mergevars', 'EMAIL']);

    foreach ($lists as $list) {
      $this->mailchimpHelper->addTagsMember($list->id, $this->tags, $email);
    }

    if ($this->url) {
      $form_state->setRedirectUrl($this->url);
    }
  }

}
