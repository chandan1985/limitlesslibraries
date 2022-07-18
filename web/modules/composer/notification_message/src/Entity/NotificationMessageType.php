<?php

namespace Drupal\notification_message\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Define the notification message type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "notification_message_type",
 *   label = @Translation("Notification message types"),
 *   bundle_of = "notification_message",
 *   admin_permission = "administer notification message types",
 *   config_prefix = "type",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "label" = "label",
 *   },
 *   config_export = {
 *     "id",
 *     "help",
 *     "label",
 *     "description",
 *     "allow_condition",
 *     "condition_datatype",
 *   },
 *   handlers = {
 *     "form" = {
 *       "add" = "\Drupal\notification_message\Form\NotificationMessageTypeForm",
 *       "edit" = "\Drupal\notification_message\Form\NotificationMessageTypeForm",
 *       "delete" = "\Drupal\notification_message\Form\NotificationMessageTypeDeleteForm",
 *     },
 *     "list_builder" = "\Drupal\notification_message\Controller\NotificationMessageTypeListBuilder",
 *     "route_provider" = {
 *       "html" = "\Drupal\notification_message\Entity\Routing\NotificationMessageHtmlRouteProvider"
 *     }
 *   },
 *   links = {
 *     "collection" = "/admin/structure/notification-message-types",
 *     "add-form" = "/admin/structure/notification-message-types/add",
 *     "edit-form" = "/admin/structure/notification-message-types/{notification_message_type}",
 *     "delete-form" = "/admin/structure/notification-message-types/{notification_message_type}/delete"
 *   }
 * )
 */
class NotificationMessageType extends ConfigEntityBundleBase implements NotificationMessageTypeInterface {

  /**
   * Notification message type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * Notification message type label.
   *
   * @var string
   */
  protected $label;

  /**
   * Notification message type help.
   *
   * @var string
   */
  protected $help;

  /**
   * Notification message type description.
   *
   * @var string
   */
  protected $description;

  /**
   * Notification message type allow condition.
   *
   * @var bool
   */
  protected $allow_condition;

  /**
   * Notification message type condition data types.
   *
   * @var array
   */
  protected $condition_datatype;

  /**
   * {@inheritDoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritDoc}
   */
  public function setDescription($description) {
    $this->description = $description;
  }

  /**
   * {@inheritDoc}
   */
  public function getHelpDescription() {
    return $this->help;
  }

  /**
   * {@inheritDoc}
   */
  public function getAllowCondition() {
    return $this->allow_condition;
  }

  /**
   * {@inheritDoc}
   */
  public function getConditionDatatype() {
    return $this->condition_datatype ?? [];
  }

  /**
   * {@inheritDoc}
   */
  public function hasAssociatedData() {
    $query = $this->notificationMessageQuery();
    return (bool) $query->condition('type', $this->id())->count()->execute() != 0;
  }

  /**
   * Get the notification message.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   Return the notification message query.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function notificationMessageQuery() {
    return $this->entityTypeManager()
      ->getStorage('notification_message')
      ->getQuery();
  }

}
