<?php

namespace Drupal\notification_message\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Define the notification message type builder.
 */
class NotificationMessageTypeListBuilder extends EntityListBuilder {

  /**
   * {@inheritDoc}
   */
  public function buildHeader() {
    return [
      'label' => $this->t('Label'),
    ] + parent::buildHeader();
  }

  /**
   * {@inheritDoc}
   */
  public function buildRow(EntityInterface $entity) {
    return [
      'label' => $entity->label(),
    ] + parent::buildRow($entity);
  }

}
