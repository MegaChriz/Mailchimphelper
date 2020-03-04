<?php

namespace Drupal\mailchimphelper_test;

use ReflectionClass;

/**
 * Autoloader for mailchimp classes.
 */
class Autoloader {

  /**
   * An autoloader function looking for Mailchimp test classes.
   *
   * @param string $fq_class
   *   A fully qualified class name.
   */
  public static function searchClass($fq_class) {
    if (strpos($fq_class, 'Mailchimp\Tests') === 0) {
      // First check if the class is locally available.
      $dirs = [__DIR__ . '/Mailchimp/'];

      // Then check the mailchimp library.
      $reflector = new ReflectionClass('Mailchimp\Mailchimp');
      $dirs[] = dirname(dirname($reflector->getFileName())) . '/tests/src/';

      $parts = explode('\\', $fq_class, 3);
      $file_part = strtr($parts[2], '\\', '/') . '.php';
      foreach ($dirs as $dir) {
        if (file_exists($dir . $file_part)) {
          require_once $dir . $file_part;
          return;
        }
      }
    }
  }

}
