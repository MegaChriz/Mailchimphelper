<?php

namespace Drupal\mailchimphelper\Tests\Rules;

use Drupal\mailchimphelper\Tests\TestBase;

/**
 * Base class for Rules related tests.
 */
class RulesTestBase extends TestBase {
  /**
   * {@inheritdoc}
   */
  protected function setUp($modules = array()) {
    $modules = array_merge($modules, array(
      'rules',
      'rules_admin',
    ));
    parent::setUp($modules);
  }

  /**
   * Creates a test rule.
   *
   * @param string $event
   *   The event to react on.
   *
   * @return \RulesReactionRule
   *   An instance of RulesReactionRule.
   */
  protected function createTestRule($event) {
    $rule = rules_reaction_rule();
    $rule->event($event);
    return $rule;
  }
}
