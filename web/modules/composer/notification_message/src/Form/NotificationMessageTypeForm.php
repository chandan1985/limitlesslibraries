<?php

namespace Drupal\notification_message\Form;

use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Plugin\Context\ContextDefinitionInterface;
use Drupal\notification_message\Entity\NotificationMessageType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Define the notification message form type.
 */
class NotificationMessageTypeForm extends BundleEntityFormBase {

  use MessengerTrait;

  /**
   * @var \Drupal\notification_message\Form\ConditionManager
   */
  protected $conditionManager;

  /**
   * Notification message type form constructor.
   *
   * @param \Drupal\Core\Condition\ConditionManager $condition_manager
   *   The condition manager service.
   */
  protected function __construct(ConditionManager $condition_manager) {
    $this->conditionManager = $condition_manager;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    $condition_manager = $container->get('plugin.manager.condition');
    assert($condition_manager instanceof ConditionManager);
    return new static($condition_manager);
  }

  /**
   * {@inheritDoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\notification_message\Entity\NotificationMessageType $entity */
    $entity = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $entity->label(),
      '#description' => $this->t(
        "Input a name for the %content_entity_id entity type.",
        ['%content_entity_id' => $entity->getEntityType()->getBundleOf()]
      ),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\notification_message\Entity\NotificationMessageType::load',
      ],
      '#disabled' => !$entity->isNew(),
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#description' => $this->t('Input a short description describing the
        notification message type.'),
      '#default_value' => $entity->getDescription(),
    ];
    $form['help'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Help Description'),
      '#description' => $this->t('Input a short help description at the top
        of the entity edit form.'),
      '#default_value' => $entity->getHelpDescription(),
    ];
    $form['allow_condition'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow Conditions'),
      '#description' => $this->t('If checked the condition options will be
        displayed on the entity form.'),
      '#default_value' => $entity->getAllowCondition() ?? TRUE,
    ];
    $form['condition_datatype'] = [
      '#type' => 'select',
      '#title' => $this->t('Condition DataTypes'),
      '#description' => $this->t("Select the data types to show conditions for on the entity form.<br/><strong>Note:</strong> Only conditions that don\'t require a context will be shown if no data types are selected."),
      '#multiple' => TRUE,
      '#options' => $this->getConditionDataTypeOptions(),
      '#empty_option' => $this->t('- All -'),
      '#default_value' => $entity->getConditionDatatype(),
      '#states' => [
        'visible' => [
          ':input[name="allow_condition"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return $this->protectBundleIdElement($form);
  }

  /**
   * {@inheritDoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = parent::save($form, $form_state);

    $entity = $this->entity;
    assert($entity instanceof NotificationMessageType);

    $this->messenger()->addStatus(
      $this->t('The %label notification message type has successfully been %action.', [
        '%label' => $entity->label(),
        '%action' => $status === SAVED_NEW ? 'created' : 'updated',
      ])
    );

    $form_state->setRedirectUrl($entity->toUrl('collection'));
  }

  /**
   * Get condition datatype options.
   *
   * @return array
   *   An array of condition data type options.
   */
  protected function getConditionDataTypeOptions() {
    $options = [];

    foreach ($this->conditionManager->getDefinitions() as $condition) {
      if (!isset($condition['context_definitions'])) {
        continue;
      }
      foreach ($condition['context_definitions'] as $context) {
        assert($context instanceof ContextDefinitionInterface);
        if (!$context->isRequired()) {
          continue;
        }
        $data_type = $context->getDataType();

        if (!isset($options[$data_type])) {
          $options[$data_type] = $data_type;
        }
      }
    }

    return $options;
  }

}
