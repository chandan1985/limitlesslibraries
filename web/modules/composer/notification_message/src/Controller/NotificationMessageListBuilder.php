<?php

namespace Drupal\notification_message\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Define the notification message list builder controller.
 */
class NotificationMessageListBuilder extends EntityListBuilder {

  /**
   * {@inheritDoc}
   */
  public function buildHeader() {
    return [
      'label' => $this->t('Label'),
      'type' => $this->t('Type'),
      'status' => $this->t('Status'),
      'condition' => $this->t('Has Condition'),
      'publish_date' => $this->t('Publish Date'),
      'unpublish_date' => $this->t('Unpublish Date'),
    ] + parent::buildHeader();
  }

  /**
   * {@inheritDoc}
   */
  public function buildRow(EntityInterface $entity) {
    return [
      'label' => $entity->label(),
      'type' => $entity->getBundleEntityTypeEntity()->label(),
      'status' => $entity->isPublished() ? 'Published' : 'Unpublished',
      'condition' => $entity->hasConditions() ? 'True' : 'False',
      'publish_date' => $entity->getPublishStartDateFormat('m/d/y g:i:s A'),
      'unpublish_date' => $entity->getPublishEndDateFormat('m/d/y g:i:s A'),
    ] + parent::buildRow($entity);
  }

}
