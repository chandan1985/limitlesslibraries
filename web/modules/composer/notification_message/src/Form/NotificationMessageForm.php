<?php

namespace Drupal\notification_message\Form;

use Drupal\Core\Condition\ConditionInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\EntityContextDefinition;
use Drupal\notification_message\Entity\NotificationMessageType;

/**
 * Define the notification message default entity form.
 */
class NotificationMessageForm extends ContentEntityForm {

  /**
   * {@inheritDoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\notification_message\Entity\NotificationMessageInterface $entity */
    $entity = $this->entity;

    /** @var \Drupal\notification_message\Entity\NotificationMessageType $bundle_entity_type */
    $bundle_entity_type = $entity->getBundleEntityTypeEntity();

    if ($help_markup = $bundle_entity_type->getHelpDescription()) {
      $form['#markup'] = $help_markup;
    }
    $form['conditions_required']['#access'] = FALSE;

    if ($bundle_entity_type->getAllowCondition()) {
      $conditions = $entity->getConditions();

      $form['conditions'] = [
        '#type' => 'container',
        '#tree' => TRUE,
      ];
      $form['conditions']['groups'] = [
        '#type' => 'vertical_tabs',
        '#title' => $this->t('Notification Conditions'),
      ];
      foreach ($this->getConditionDefinitions() as $plugin_id => $definition) {
        if (!isset($definition['label'])) {
          continue;
        }
        $form['conditions'][$plugin_id] = [
          '#type' => 'details',
          '#title' => $definition['label'],
          '#group' => 'conditions][groups',
        ];
        $configurations = $conditions[$plugin_id]['configuration'] ?? [];

        $instance = $this
          ->conditionManager()
          ->createInstance($plugin_id, $configurations);

        if ($instance instanceof ConditionInterface) {
          $subform = [
            '#parents' => [
              'conditions',
              $plugin_id,
              'configuration',
            ],
          ];
          $form['conditions'][$plugin_id]['configuration'] = $instance
            ->buildConfigurationForm(
              $subform,
              SubformState::createForSubform($subform, $form, $form_state)
            );

          /* TODO: Remove workaround once https://www.drupal.org/project/drupal/issues/2783897 is fixed. */
          if ($plugin_id === 'current_theme') {
            $form['conditions'][$plugin_id]['configuration']['theme']['#empty_option'] = $this->t('- None -');
          }
        }
      }
      $form['conditions_required']['#access'] = TRUE;
    }

    $form['message_options'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Notification Options'),
      '#weight' => 100,
    ];
    $form['message_publish'] = [
      '#type' => 'details',
      '#group' => 'message_options',
      '#title' => $this->t('Message Publish'),
    ];

    if (isset($form['path'])) {
      $form['message_path'] = [
        '#type' => 'details',
        '#group' => 'message_options',
        '#title' => $this->t('Message Path'),
      ];
      $form['path']['#group'] = 'message_path';
    }

    $form['message_information'] = [
      '#type' => 'details',
      '#group' => 'message_options',
      '#title' => $this->t('Message Information'),
    ];

    if (isset($form['uid'])) {
      $form['uid']['#group'] = 'message_information';
    }

    if (isset($form['created'])) {
      $form['created']['#group'] = 'message_information';
    }

    if (isset($form['publish_end_date'])) {
      $form['publish_end_date']['#group'] = 'message_publish';
    }

    if (isset($form['publish_start_date'])) {
      $form['publish_start_date']['#group'] = 'message_publish';
    }

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $form_state->addCleanValueKey(['conditions', 'groups'])->cleanValues();

    $entity = parent::validateForm($form, $form_state);
    $condition_manager = $this->conditionManager();

    foreach ($entity->getConditions() as $plugin_id => $info) {
      if (!isset($info['configuration'])
        || !$condition_manager->hasDefinition($plugin_id)) {
        continue;
      }

      /** @var ConditionInterface $instance */
      $instance = $condition_manager->createInstance(
        $plugin_id, $info['configuration']
      );

      if ($instance instanceof ConditionInterface) {
        $subform = [
          '#parents' => [
            'conditions',
            $plugin_id,
            'configuration',
          ],
        ];
        $instance->validateConfigurationForm(
          $subform,
          SubformState::createForSubform($subform, $form, $form_state)
        );
      }
    }
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->hasValue('conditions')) {
      $condition_manager = $this->conditionManager();

      foreach ($form_state->getValue('conditions') as $plugin_id => $info) {
        if (!isset($info['configuration'])
          || !$condition_manager->hasDefinition($plugin_id)) {
          continue;
        }

        $instance = $condition_manager->createInstance(
          $plugin_id, $info['configuration']
        );

        if ($instance instanceof ConditionInterface) {
          $subform = [
            '#parents' => [
              'conditions',
              $plugin_id,
              'configuration',
            ],
          ];

          $instance->submitConfigurationForm(
            $subform,
            SubformState::createForSubform($subform, $form, $form_state)
          );

          // The condition plugin is responsible for submitting their own
          // configs, so we'll need to update the values in the form state.
          $form_state->setValue(
            $subform['#parents'], $instance->getConfiguration()
          );
        }
      }
    }
    parent::submitForm($form, $form_state);

    $form_state->setRedirect('entity.notification_message.collection');
  }

  /**
   * Get the condition definitions based on the context.
   *
   * @return array
   *   An array of condition definitions filtered by context.
   */
  protected function getConditionDefinitions() {
    return $this->conditionManager()
      ->getDefinitionsForContexts(
        $this->getConditionContexts()
      );
  }

  /**
   * Get the condition contexts.
   *
   * @return array
   *   An array of condition contexts.
   */
  protected function getConditionContexts() {
    $contexts = [];

    $bundle_entity = $this->entity->getBundleEntityTypeEntity();
    assert($bundle_entity instanceof NotificationMessageType);
    foreach ($bundle_entity->getConditionDatatype() as $data_type) {
      $contexts[] = new Context(new EntityContextDefinition($data_type));
    }

    return $contexts;
  }

  /**
   * Define the condition manager.
   *
   * @return \Drupal\Core\Condition\ConditionManager
   *   The condition manager service.
   */
  protected function conditionManager() {
    return \Drupal::service('plugin.manager.condition');
  }

}
