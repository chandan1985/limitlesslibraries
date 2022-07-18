<?php

namespace Drupal\notification_message\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Define the notification message type delete form.
 */
class NotificationMessageTypeDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritDoc
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete %label?', [
      '%label' => $this->entity->label(),
    ]);
  }

  /**
   * {@inheritDoc}
   */
  public function getCancelUrl() {
    $this->entity->toUrl('collection');
  }

  /**
   * {@inheritDoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    /** @var \Drupal\notification_message\Entity\NotificationMessageType $entity */
    $entity = $this->entity;

    if ($entity->hasAssociatedData()) {
      $form_state->setError(
        $form, $this->t('Unable to delete, due to notification messages existing for the @label type.', [
          '@label' => $entity->label(),
        ]),
      );
    }
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->entity->delete();
  }

}
