<?php

namespace Drupal\notification_message\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\notification_message\Entity\NotificationMessage;
use Drupal\notification_message\Entity\NotificationMessageType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Define the notification message queue block.
 *
 * @Block(
 *   id = "notification_message",
 *   admin_label = @Translation("Notification messages")
 * )
 */
class NotificationMessageBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * NotificationMessageBlock constructor.
   *
   * @param array $configuration
   *   The block configurations.
   * @param string $plugin_id
   *   The block plugin identifier.
   * @param array $plugin_definition
   *   The block plugin definition.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, EntityDisplayRepositoryInterface $entity_display_repository, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityDisplayRepository = $entity_display_repository;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $entity_display_repository = $container->get('entity_display.repository');
    assert($entity_display_repository instanceof EntityDisplayRepositoryInterface);
    $entity_type_manager = $container->get('entity_type.manager');
    assert($entity_type_manager instanceof EntityTypeManagerInterface);
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $entity_display_repository,
      $entity_type_manager,
    );
  }

  /**
   * {@inheritDoc}
   */
  public function defaultConfiguration() {
    return [
      'notification_message' => [
        'type' => [],
        'display_mode' => 'full',
      ],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritDoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $form['notification_message'] = [
      '#type' => 'details',
      '#title' => $this->t('Notification Message'),
      '#open' => TRUE,
    ];
    $form['notification_message']['display_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Display Mode'),
      '#required' => TRUE,
      '#description' => $this->t('Select the notification message view mode.'),
      '#options' => $this->getDisplayModeOptions(),
      '#default_value' => $this->getNotificationMessageDisplayMode(),
    ];
    $form['notification_message']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Message Type'),
      '#multiple' => TRUE,
      '#description' => $this->t('Select the notification message types.<br/><strong>Note:</strong> If no message types are selected then all valid messages are rendered.'),
      '#options' => $this->getNotificationMessageTypeOptions(),
      '#empty_option' => $this->t('- Select -'),
      '#default_value' => $this->getNotificationMessageType(),
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['notification_message'] = $form_state->getValue('notification_message');
  }

  /**
   * {@inheritDoc}
   */
  public function build() {
    $messages = $this->loadMessages();
    $cacheability = new CacheableMetadata();
    $cacheability->addCacheTags(['notification_message_list']);
    $accessible = [];
    foreach ($messages as $message) {
      $access_result = $message->access('view', NULL, TRUE);
      $cacheability->addCacheableDependency($access_result);
      if ($access_result->isAllowed()) {
        array_push($accessible, $message);
      }
    }
    $build = [
      '#block' => $this,
      '#theme' => 'notification_messages',
      '#messages' => $this->viewMessages($accessible),
    ];
    $cacheability->applyTo($build);
    return $build;
  }

  /**
   * Loads all notification messages.
   *
   * @return \Drupal\notification_message\Entity\NotificationMessageInterface[]
   *   All notification messages.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function loadMessages(): array {
    $storage = $this->entityTypeManager->getStorage('notification_message');
    $query = $storage->getQuery();
    $message_type = $this->getNotificationMessageType();
    if (!empty($message_type)) {
      $query->condition('type', (array) $message_type, 'IN');
    }
    $query->accessCheck();
    $messages = $storage->loadMultiple($query->execute());

    return array_filter($messages, static function (NotificationMessage $message) {
      return $message->isPublished();
    });
  }

  /**
   * Renders an array of notification_message entities.
   *
   * @param \Drupal\notification_message\Entity\NotificationMessageInterface[] $messages
   *   The messages to be rendered.
   *
   * @return array
   *   A render array representing notification messages.
   */
  protected function viewMessages(array $messages): array {
    return $this->entityTypeManager->getViewBuilder('notification_message')->viewMultiple($messages, $this->getNotificationMessageDisplayMode());
  }

  /**
   * Get notification message type.
   *
   * @return string
   *   The notification message type.
   */
  public function getNotificationMessageType() {
    return $this->configuration['notification_message']['type'];
  }

  /**
   * Get notification message display mode.
   *
   * @return string
   *   The notification message display view mode.
   */
  public function getNotificationMessageDisplayMode() {
    return $this->configuration['notification_message']['display_mode'];
  }

  /**
   * Get notification message display mode options.
   *
   * @return array
   *   An array of display mode options.
   */
  protected function getDisplayModeOptions() {
    return $this->entityDisplayRepository->getViewModeOptions('notification_message');
  }

  /**
   * Get the notification message type options.
   *
   * @return array
   *   An array of notification message types.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getNotificationMessageTypeOptions() {
    $options = [];

    foreach ($this->entityTypeManager->getStorage('notification_message_type')->loadMultiple() as $id => $entity) {
      assert($entity instanceof NotificationMessageType);
      $options[$id] = $entity->label();
    }

    return $options;
  }

}
