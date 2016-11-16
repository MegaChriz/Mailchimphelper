<?php

namespace Drupal\mailchimphelper\Tests;

/**
 * Tests module installation and uninstallation.
 */
class ModuleInstallUninstallTest extends TestBase {

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'name' => 'Module installation and uninstallation',
      'description' => '',
      'group' => 'MailChimp Helper',
    );
  }

  /**
   * Test installation and uninstallation.
   */
  function testInstallationAndUninstallation() {
    $this->assertTrue(module_exists('mailchimphelper'));

    // Test default configuration.
    // @todo

    module_disable(array('mailchimphelper'));
    drupal_uninstall_modules(array('mailchimphelper'));
    $this->assertFalse(module_exists('mailchimphelper'));
  }
}
