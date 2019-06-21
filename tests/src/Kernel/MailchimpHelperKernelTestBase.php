<?php

namespace Drupal\Tests\mailchimphelper\Kernel;

use Drupal\mailchimp_test\MailchimpConfigOverrider;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Provides a base class for kernel tests.
 */
abstract class MailchimpHelperKernelTestBase extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'mailchimp',
    'mailchimp_test',
    'mailchimphelper',
    'mailchimphelper_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig(['mailchimp']);
    \Drupal::configFactory()->addOverride(new MailchimpConfigOverrider());
  }

}
