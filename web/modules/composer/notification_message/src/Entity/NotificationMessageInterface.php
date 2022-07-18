<?php

namespace Drupal\notification_message\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;

/**
 * Define the notification message interface.
 */
interface NotificationMessageInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface {

  /**
   * Default value callback for 'uid' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return array
   *   An array of current user default values.
   */
  public static function getCurrentUserId();

  /**
   * Render the notification message.
   *
   * @param string $view_mode
   *   The entity view mode.
   * @param null $langcode
   *   The entity language code.
   *
   * @return array
   *   The render array for the notification message.
   */
  public function view($view_mode = 'full', $langcode = NULL);

  /**
   * Get the notification message author user.
   *
   * @return \Drupal\user\Entity\User
   *   The notification message user entity.
   */
  public function getAuthorUser();

  /**
   * Get publish end date format.
   *
   * @param $format
   *   The date format.
   *
   * @return false|string
   *   The publish end date in the specified format.
   */
  public function getPublishEndDateFormat($format);

  /**
   * Get publish start date format.
   *
   * @param $format
   *   The date format.
   *
   * @return false|string
   *   The publish start date in the specified format.
   */
  public function getPublishStartDateFormat($format);

  /**
   * Get the bundle entity type entity.
   *
   * @return \Drupal\notification_message\Entity\NotificationMessageType
   *   The entity bundle type entity instance.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getBundleEntityTypeEntity();

  /**
   * Get the notification message configurations.
   *
   * @return array
   *   An array of conditions.
   */
  public function getConditions();

  /**
   * Has notification message conditions.
   *
   * @return bool
   *   Return TRUE if the notification message has conditions; otherwise FALSE.
   */
  public function hasConditions();

  /**
   * Determine if all conditions are required.
   *
   * @return bool
   *   Return TRUE if all conditions are required; otherwise FALSE.
   */
  public function conditionsRequired();

  /**
   * Evaluate notification message conditions.
   *
   * @param array $contexts
   *   An array of contexts to pass to condition plugins.
   *
   * @return bool
   *   Return TRUE if the condition evaluated; otherwise FALSE.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function evaluateConditions(array $contexts = []);

}
