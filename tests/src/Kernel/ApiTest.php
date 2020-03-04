<?php

namespace Drupal\Tests\mailchimphelper\Kernel;

/**
 * Tests core API functionality.
 *
 * @group mailchimphelper
 */
class ApiTest extends MailchimpHelperKernelTestBase {

  /**
   * Tests that the test API has been loaded.
   */
  public function testApi() {
    $mailchimp_api = mailchimp_get_api_object();

    $this->assertNotNull($mailchimp_api);
    $this->assertEquals('Mailchimp\Tests\Mailchimp', get_class($mailchimp_api));

    // Check if there are some lists.
    $this->assertCount(3, mailchimp_get_lists());
  }

}
