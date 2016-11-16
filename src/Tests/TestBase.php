<?php

namespace Drupal\mailchimphelper\Tests;

use \stdClass;
use DrupalWebTestCase;
use Drupal\mailchimphelper\Tests\MailChimp\MailchimpLists;

/**
 * Base class for tests.
 */
abstract class TestBase extends DrupalWebTestCase {
  /**
   * The main list id used in MailChimp.
   *
   * @var string
   */
  protected $listId = '57afe96172';

  /**
   * @var \Drupal\mailchimphelper\Tests\MailChimp\MailchimpLists;
   */
  protected $list;

  /**
   * Pre-test setup function.
   *
   * Enables dependencies.
   * Sets the mailchimp_api_key to the test-mode key.
   * Sets test mode to TRUE.
   */
  protected function setUp($modules = array()) {
    // Use a profile that contains required modules.
    $prof = drupal_get_profile();
    $this->profile = $prof;

    // Enable modules required for the test.
    $modules = array_merge($modules, array(
      'psr0',
      'libraries',
      'mailchimp',
      'entity',
      'entity_token',
      'mailchimp_lists',
      'mailchimphelper',
      'mailchimphelpertest',
    ));
    parent::setUp($modules);

    variable_set('mailchimp_api_key', 'MAILCHIMP_TEST_API_KEY');
    variable_set('mailchimp_test_mode', TRUE);

    // Override the MailChimp list class to use.
    mailchimp_get_api_object('MailchimpLists');
    $this->list = &drupal_static('mailchimp_get_api_object');
    if (!($this->list instanceof MailchimpLists)) {
      $this->list = new MailchimpLists('MAILCHIMP_TEST_API_KEY', 'apikey', 60);
    }
  }

  /**
   * Post-test function.
   *
   * Sets test mode to FALSE.
   */
  protected function tearDown() {
    parent::tearDown();
    variable_del('mailchimp_test_mode');
  }
}
