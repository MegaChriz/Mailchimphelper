<?php

namespace Drupal\Tests\mailchimphelper\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Provides a base class for functional tests.
 */
abstract class MailchimpHelperBrowserTestBase extends BrowserTestBase {

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

    \Drupal::configFactory()->getEditable('mailchimp.settings')
      ->set('api_key', 'MAILCHIMP_TEST_API_KEY')
      ->set('cron', FALSE)
      ->set('batch_limit', 100)
      ->set('test_mode', TRUE)
      ->save();
  }

}
