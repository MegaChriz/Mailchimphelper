<?php

namespace Drupal\mailchimphelper\TestHelpers;

use \Exception;
use \ReflectionClass;

/**
 * Util functions for tests.
 */
class Util {
  /**
   * Overrides MailChimp lists class with mailchimphelper's one.
   *
   * @return \Drupal\mailchimphelper\TestHelpers\MailchimpLists
   *   A lists instance.
   */
  public static function &overrideMailChimpLists() {
    static::register();

    // Override the MailChimp list class to use.
    mailchimp_get_api_object('MailchimpLists');
    if (!class_exists('Mailchimp\Tests\MailchimpLists')) {
      throw new Exception('Cannot run test because the MailChimp Library must be installed with dev dependencies.');
    }
    $list = &drupal_static('mailchimp_get_api_object');
    if (!($list instanceof MailchimpLists)) {
      $list = new MailchimpLists('MAILCHIMP_TEST_API_KEY', 'apikey', 60);
    }

    return $list;
  }

  /**
   * Registers this class as an autoloader.
   */
  public static function register() {
    spl_autoload_register(array(__CLASS__, 'loadClass'));
  }

  /**
   * Unregisters this class as an autoloader.
   */
  public function unregister() {
    spl_autoload_unregister(array(__CLASS__, 'loadClass'));
  }

  /**
   * Classloader for MailChimp test classes.
   *
   * @param string $class
   *   The name of the class to load.
   */
  public static function loadClass($class) {
    if (strpos($class, 'Mailchimp\\Tests') === 0 && class_exists('Mailchimp\\Mailchimp')) {
      // Find out where the mailchimp library is defined.
      $reflector = new ReflectionClass('Mailchimp\\Mailchimp');
      $dir = dirname(dirname($reflector->getFileName())) . '/tests/src';

      $parts = explode('\\', $class);
      $class_without_base = implode('\\', array_slice($parts, 2));
      $file = $dir . '/' . strtr($class_without_base, '\\', '/') . '.php';

      if (file_exists($file)) {
        require_once $file;
        return;
      }
    }
  }
}
