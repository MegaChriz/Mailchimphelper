<?php

namespace Drupal\Tests\mailchimphelper\Functional;

use Drupal\Component\Utility\NestedArray;
use Drupal\mailchimp_signup\Entity\MailchimpSignup;

/**
 * Tests for signup forms.
 *
 * @group mailchimphelper
 */
class SignupTest extends MailchimphelperBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'mailchimp',
    'mailchimp_test',
    'mailchimp_signup',
    'mailchimphelper',
    'mailchimphelper_test',
  ];

  /**
   * The operating user account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The admin account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $admin;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create an account who may signup for newsletters.
    $this->account = $this->drupalCreateUser(['access mailchimp signup pages']);

    // Create an account who may administer signup.
    $this->admin = $this->drupalCreateUser(['administer mailchimp', 'administer mailchimp signup entities']);
  }

  /**
   * Creates a new signup form.
   *
   * @param array $settings
   *   Settings for the signup form.
   *
   * @return \Drupal\mailchimp_signup\Entity\MailchimpSignup
   *   A signup form.
   */
  protected function createSignup(array $settings = []) {
    $default_settings = [
      'id' => 'signup',
      'title' => 'Signup',
      'mode' => MAILCHIMP_SIGNUP_PAGE,
      'mc_lists' => [
        '57afe96172' => '57afe96172',
      ],
      'mergefields' => [
        'EMAIL' => TRUE,
      ],
      'settings' => [
        'path' => 'signup',
        'submit_button' => 'Submit',
        'confirmation_message' => 'You have been successfully subscribed.',
        'destination' => '',
        'mergefields' => [
          'EMAIL' => 'O:8:"stdClass":8:{s:3:"tag";s:5:"EMAIL";s:4:"name";s:13:"Email Address";s:4:"type";s:5:"email";s:8:"required";b:1;s:13:"default_value";s:0:"";s:6:"public";b:1;s:13:"display_order";i:1;s:7:"options";O:8:"stdClass":1:{s:4:"size";i:25;}}',
        ],
        'doublein' => FALSE,
        'include_interest_groups' => TRUE,
        'safe_interest_groups' => TRUE,
      ],
      'third_party_settings' => [
        'mailchimphelper' => [
          'default_interest_groups' => [
            '57afe96172' => [
              'a1e9f4b7f6' => [
                '9143cf3bd1' => '9143cf3bd1',
              ],
            ],
          ],
        ],
      ],
    ];
    $settings = NestedArray::mergeDeep($default_settings, $settings);

    $signup = MailchimpSignup::create($settings);
    $signup->save();

    return $signup;
  }

  /**
   * Tests what an admin sees.
   *
   * @todo add assertions.
   */
  public function _testAdmin() {
    $this->drupalLogin($this->admin);
    $this->drupalGet('admin/config/services/mailchimp');
    $this->drupalGet('admin/config/services/mailchimp/signup/add');
  }

  /**
   * Tests subscribing single list with interest groups set as default value.
   */
  public function testSubscribeWithDefaultValueSingeList() {
    $this->createSignup();
    $this->container->get('router.builder')->rebuild();

    $this->drupalLogin($this->account);
    $edit = [
      'mergevars[EMAIL]' => 'test@example.com',
    ];
    $this->drupalPostForm('signup', $edit, 'Submit');

    $uri = 'https://us1.api.mailchimp.com/3.0/lists/57afe96172/members/55502f40dc8b7c769880b10874abc9d0';
    $expected_interests = (object) [
      '9143cf3bd1' => '1',
      '9143cf3bd2' => '',
      '9143cf3bd3' => '',
    ];
    $requests = \Drupal::state()->get('mailchimp_requests', []);
    $this->assertEquals($expected_interests, $requests[$uri][1]['options']['json']->interests);
  }

  /**
   * Tests subscribing single list with interest groups not displayed.
   */
  public function testSubscribeWithInterestGroupsNotDisplayed() {
    $this->createSignup([
      'settings' => [
        'include_interest_groups' => FALSE,
      ],
    ]);
    $this->container->get('router.builder')->rebuild();

    $this->drupalLogin($this->account);
    $edit = [
      'mergevars[EMAIL]' => 'test@example.com',
    ];
    $this->drupalPostForm('signup', $edit, 'Submit');

    $uri = 'https://us1.api.mailchimp.com/3.0/lists/57afe96172/members/55502f40dc8b7c769880b10874abc9d0';
    $expected_interests = (object) [
      '9143cf3bd1' => TRUE,
    ];
    $requests = \Drupal::state()->get('mailchimp_requests', []);
    $this->assertEquals($expected_interests, $requests[$uri][1]['options']['json']->interests);
  }

}
