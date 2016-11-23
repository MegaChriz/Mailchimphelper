<?php

namespace Drupal\mailchimphelper\Tests\Rules;

use Drupal\mailchimphelper\MailChimp\MailChimpList;

/**
 * Tests module installation and uninstallation.
 */
class SubscribeInterestGroupsMultipleTest extends RulesTestBase {

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'name' => 'Tests rules action mailchimphelper_lists_subscribe_intgroup_multiple',
      'description' => 'Covers Drupal\mailchimphelper\Plugin\Rules\RulesAction\SubscribeInterestGroupsMultiple',
      'group' => 'MailChimp Helper',
      'dependencies' => array('rules', 'psr0'),
    );
  }

  /**
   * Tests rules action 'mailchimphelper_mail_subscribe_list'.
   */
  public function test() {
    $rule = $this->createTestRule('user_insert');
    $rule->action('mailchimphelper_lists_subscribe_intgroup_multiple', array(
      'list_id' => $this->listId,
      'email:select' => 'account:mail',
      'groups' => [
        'cat1' => [
          'int1dot1' => 'int1dot1',
        ],
        'cat2' => [
          'int2dot2' => 'int2dot2',
        ],
      ],
    ));
    $rule->integrityCheck()->save();

    // Save an account to trigger rule.
    $account = $this->drupalCreateUser();

    // Assert member being subscribed.
    $list = new MailChimpList($this->listId);
    $member = $list->getMember($account->mail, TRUE);
    $this->assertEqual($account->mail, $member->getMailAddress());
    $this->assertEqual('subscribed', $member->getStatus());

    // Assert interest groups.
    $interests = get_object_vars($member->getGroups());
    ksort($interests);
    $this->assertEqual([
      'int1dot1' => TRUE,
      'int1dot2' => FALSE,
      'int2dot1' => FALSE,
      'int2dot2' => TRUE,
    ], $interests);

    // Login as admin to check Rules interface.
    $admin = $this->drupalCreateUser(['administer rules', 'administer users', 'administer mailchimp']);
    $this->drupalLogin($admin);
    $this->drupalGet('admin/config/workflow/rules');
    $this->drupalGet('admin/config/workflow/rules/reaction/manage/1');
    $this->clickLink('Subscribe email to multiple mailchimp groups');
  }
}
