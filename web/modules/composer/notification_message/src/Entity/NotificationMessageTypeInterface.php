<?php

namespace Drupal\notification_message\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityDescriptionInterface;

/**
 * Define the notification message type interface.
 */
interface NotificationMessageTypeInterface extends ConfigEntityInterface, EntityDescriptionInterface {

  /**
   * Get notification message type help description.
   *
   * @return string
   *   The notification message type help description.
   */
  public function getHelpDescription();

  /**
   * Get the notification message type allow condition flag.
   *
   * @return bool
   *   Return TRUE if conditions are allowed; otherwise FALSE.
   */
  public function getAllowCondition();

  /**
   * Get the notification message type allowed condition data types.
   *
   * @return array
   *   An array of allowed data types to filter the conditions.
   */
  public function getConditionDatatype();

  /**
   * Has associated notification messages based on the message type.
   *
   * @return bool
   *   Return TRUE if associated data exist; otherwise FALSE.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function hasAssociatedData();

}
