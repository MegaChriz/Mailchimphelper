<?php

namespace Drupal\mailchimphelper\Tests\Rules;

use Drupal\mailchimphelper\MailChimp\MailChimpList;

/**
 * Tests module installation and uninstallation.
 */
class MailSubscribeListTest extends RulesTestBase {

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'name' => 'Tests rules action mailchimphelper_mail_subscribe_list',
      'description' => 'Covers Drupal\mailchimphelper\Plugin\Rules\RulesAction\MailSubscribeList',
      'group' => 'MailChimp Helper',
      'dependencies' => array('rules', 'psr0'),
    );
  }

  /**
   * Tests rules action 'mailchimphelper_mail_subscribe_list'.
   */
  public function test() {
    $rule = $this->createTestRule('user_insert');
    $rule->action('mailchimphelper_mail_subscribe_list', array(
      'list_id' => $this->listId,
      'mergevars_EMAIL:select' => 'account:mail',
      'mergevars_FNAME:select' => 'account:name',
    ));
    $rule->integrityCheck()->save();

    // Save an account to trigger rule.
    $account = $this->drupalCreateUser();

    // Assert member being subscribed.
    $list = new MailChimpList($this->listId);
    $member = $list->getMemberInfo($account->mail, TRUE);
    $this->assertEqual($account->mail, $member->getMailAddress());
    $this->assertEqual($account->name, $member->getMergeField('FNAME'));
    $this->assertEqual(NULL, $member->getMergeField('LNAME'));
    $this->assertEqual('subscribed', $member->getStatus());

    // Login as admin to check Rules interface.
    $admin = $this->drupalCreateUser(['administer rules', 'administer users', 'administer mailchimp']);
    $this->drupalLogin($admin);
    $this->drupalGet('admin/config/workflow/rules');
    $this->drupalGet('admin/config/workflow/rules/reaction/manage/1');
    $this->clickLink('Subscribe mail address to a mailchimp list');
  }
}
