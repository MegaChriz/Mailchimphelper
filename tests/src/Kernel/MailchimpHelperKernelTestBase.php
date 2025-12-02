<?php

namespace Drupal\Tests\mailchimphelper\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\mailchimp_test\MailchimpConfigOverrider;

/**
 * Provides a base class for kernel tests.
 */
abstract class MailchimpHelperKernelTestBase extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'mailchimp',
    'mailchimp_test',
    'mailchimphelper',
    'mailchimphelper_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig(['mailchimp']);
    \Drupal::configFactory()->addOverride(new MailchimpConfigOverrider());
  }

}
