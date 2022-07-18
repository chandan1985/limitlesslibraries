<?php

namespace Drupal\Tests\notification_message\Functional;

use Drupal\Component\Utility\Random;
use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Tests the notification_message module's user interface.
 *
 * @group notification_message
 */
final class NotificationMessageUiTest extends BrowserTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'notification_message',
    'notification_message_ui_test',
  ];

  /**
   * A test user with permission to create publish notification messages.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $messageCreator;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->messageCreator = $this->createUser(['administer notification message content']);
  }

  /**
   * Tests users' ability to create global messages.
   */
  public function testCreateGlobalMessage() {
    $this->drupalLogin($this->messageCreator);
    $this->createGlobalNotificationMessage();
  }

  /**
   * Tests visitors' ability to view global messages.
   *
   * @depends testCreateGlobalMessage
   */
  public function testViewGlobalMessage() {
    $notification = $this->createTestNotificationMessage();
    $this->drupalGet(Url::fromRoute('<front>'));
    $this->assertSession()->pageTextContains(trim($notification->message->value));
  }

  /**
   * Tests page cacheability with notification messages.
   *
   * This both ensures that the notification messages block is cacheable and
   * that cache metadata for its messages bubbles up to the page level.
   */
  public function testCaching() {
    $global_notification = $this->createTestNotificationMessage();
    $test_url = Url::fromRoute('notification_message_ui_test.test_page');
    $this->drupalGet($test_url);
    $this->assertSession()->pageTextContains(trim($global_notification->message->value));
    $this->assertSession()->responseHeaderNotContains('X-Drupal-Cache-Max-Age', '0 (Uncacheable)');
    $this->assertSession()->responseHeaderNotMatches('X-Drupal-Cache-Contexts', '/url\.path/');
    $path_notification = $this->createTestNotificationMessage([
      'conditions[request_path][configuration][pages]' => '/notification-message-ui-test-page',
    ]);
    $this->drupalGet($test_url);
    $this->assertSession()->pageTextContains(trim($global_notification->message->value));
    $this->assertSession()->pageTextContains(trim($path_notification->message->value));
    $this->assertSession()->responseHeaderMatches('X-Drupal-Cache-Contexts', '/url\.path/');
  }

  /**
   * Utility method for creating a test message.
   *
   * This logs in as a user that is authorized to create a message and logs out
   * before returning the newly created message.
   */
  protected function createTestNotificationMessage(array $form_values = []) {
    $this->drupalLogin($this->messageCreator);
    $notification = $this->createGlobalNotificationMessage($form_values);
    assert($notification->isPublished());
    $this->drupalLogout();
    return $notification;
  }

  /**
   * Creates a new global notification message.
   *
   * Callers should log in with a user authorized to create a message before
   * using this method.
   *
   * @return \Drupal\notification_message\Entity\NotificationMessageInterface
   *   A new notification message entity.
   */
  protected function createGlobalNotificationMessage(array $form_values = []) {
    $this->drupalGet(Url::fromRoute('entity.notification_message.add_form', [
      'notification_message_type' => 'global',
    ]));
    $random = new Random();
    $label = $random->sentences(3, TRUE);
    $input = $form_values + [
      'label[0][value]' => $label,
      'message[0][value]' => $random->paragraphs(1),
    ];
    $this->submitForm($input, 'Save');
    $this->assertSession()->pageTextContains($label);
    $messages = \Drupal::entityTypeManager()->getStorage('notification_message')->loadByProperties([
      'label' => $label,
    ]);
    return array_pop($messages);
  }

}
