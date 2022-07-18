<?php

namespace Drupal\notification_message\Entity;

use Drupal\Component\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Condition\ConditionInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Define the notification message entity.
 *
 * @ContentEntityType(
 *   id = "notification_message",
 *   label = @Translation("Notification Message"),
 *   translatable = TRUE,
 *   base_table = "notification_message",
 *   data_table = "notification_message_field_data",
 *   admin_permission = "administer notification message content",
 *   bundle_entity_type = "notification_message_type",
 *   field_ui_base_route = "entity.notification_message_type.edit_form",
 *   entity_keys = {
 *     "id" = "id",
 *     "uid" = "uid",
 *     "uuid" = "uuid",
 *     "label" = "label",
 *     "bundle" = "type",
 *     "created" = "created",
 *     "changed" = "changed",
 *     "langcode" = "langcode",
 *   },
 *   handlers = {
 *     "access" = "\Drupal\notification_message\Entity\NotificationMessageAccess",
 *     "storage" = "Drupal\notification_message\Entity\NotificationMessageStorage",
 *     "list_builder" = "\Drupal\notification_message\Controller\NotificationMessageListBuilder",
 *     "form" = {
 *       "edit": "\Drupal\notification_message\Form\NotificationMessageForm",
 *       "delete": "\Drupal\notification_message\Form\NotificationMessageDeleteForm",
 *       "default" = "\Drupal\notification_message\Form\NotificationMessageForm",
 *     },
 *     "route_provider" = {
 *       "html" = "\Drupal\notification_message\Entity\Routing\NotificationMessageHtmlRouteProvider"
 *     }
 *   },
 *   links = {
 *     "canonical" = "/notification/{notification_message}",
 *     "collection" = "/admin/content/notification-message",
 *     "add-page" = "/admin/content/notification-message/add",
 *     "add-form" = "/admin/content/notification-message/add/{notification_message_type}",
 *     "edit-form" = "/admin/content/notification-message/{notification_message}",
 *     "delete-form" = "/admin/content/notification-message/{notification_message}/delete"
 *   }
 * )
 */
class NotificationMessage extends ContentEntityBase implements NotificationMessageInterface {

  use EntityChangedTrait;

  /**
   * Instantiated condition plugins for this message.
   *
   * @var \Drupal\Core\Condition\ConditionInterface[]
   */
  protected $loadedConditions = [];

  /**
   * {@inheritDoc}
   */
  public static function getCurrentUserId() {
    return [\Drupal::currentUser()->id()];
  }

  /**
   * {@inheritDoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setRequired(TRUE)
      ->setLabel(new TranslatableMarkup('Label'))
      ->setTranslatable(TRUE)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => '-15',
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => '-10',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['message'] = BaseFieldDefinition::create('text_long')
      ->setRequired(FALSE)
      ->setLabel(new TranslatableMarkup('Message'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => '-5',
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'weight' => '-5',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Authored by'))
      ->setDescription(new TranslatableMarkup('The username of the notification message author.'))
      ->setSetting('target_type', 'user')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', FALSE)
      ->setDefaultValueCallback(__CLASS__ . '::getCurrentUserId');

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Authored on'))
      ->setDescription(new TranslatableMarkup('The time that the notification message was created.'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 5,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'timestamp',
        'region' => 'hidden',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(new TranslatableMarkup('Changed'))
      ->setDescription(new TranslatableMarkup('The time that the node was last edited.'))
      ->setTranslatable(TRUE);

    $fields['publish_start_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(new TranslatableMarkup('Publish Start Date'))
      ->setDescription(new TranslatableMarkup(
        'The date the notification message should be published.'
      ))
      ->setRequired(TRUE)
      ->setSettings([
        'datetime_type' => 'datetime',
      ])
      ->setDefaultValue([
        'default_date' => 'now',
        'default_date_type' => 'now',
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'datetime_default',
        'region' => 'hidden',
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['publish_end_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(new TranslatableMarkup('Publish End Date'))
      ->setDescription(new TranslatableMarkup(
        'The date the notification message should be unpublished.'
      ))
      ->setRequired(TRUE)
      ->setSettings([
        'datetime_type' => 'datetime',
      ])
      ->setDefaultValue([
        'default_date' => '+2 day',
        'default_date_type' => 'relative',
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'datetime_default',
        'region' => 'hidden',
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['conditions'] = BaseFieldDefinition::create('map')
      ->setRequired(FALSE)
      ->setLabel(new TranslatableMarkup('Conditions'))
      ->setDisplayOptions('form', [
        'weight' => 95,
      ]);

    $fields['conditions_required'] = BaseFieldDefinition::create('boolean')
      ->setRequired(FALSE)
      ->setLabel(new TranslatableMarkup('All conditions required'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 96,
        'settings' => [
          'display_label' => TRUE,
        ],
      ])
      ->setDisplayOptions('view', [
        'region' => 'hidden',
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    return $fields;
  }

  /**
   * {@inheritDoc}
   */
  public function isPublished() {
    $published_timestamp = $this->getPublishStartDate()->getTimestamp();
    $unpublished_timestamp = $this->getPublishEndDate()->getTimestamp();

    if (!isset($published_timestamp) || !isset($unpublished_timestamp)) {
      return FALSE;
    }
    $now = time();

    return $now >= $published_timestamp && $now <= $unpublished_timestamp;
  }

  /**
   * {@inheritDoc}
   */
  public function setUnpublished() {
    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function setPublished($published = NULL) {
    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function view($view_mode = 'full', $langcode = NULL) {
    return $this->entityViewBuilder()->view($this, $view_mode, $langcode);
  }

  /**
   * {@inheritDoc}
   */
  public function getAuthorUser() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritDoc}
   */
  public function getPublishEndDateFormat($format) {
    return $this->getPublishEndDate()->format($format, [
      'timezone' => date_default_timezone_get(),
    ]);
  }

  /**
   * {@inheritDoc}
   */
  public function getPublishStartDateFormat($format) {
    return $this->getPublishStartDate()->format($format, [
      'timezone' => date_default_timezone_get(),
    ]);
  }

  /**
   * {@inheritDoc}
   */
  public function getBundleEntityTypeEntity() {
    return $this->entityTypeManager()
      ->getStorage($this->getEntityType()->getBundleEntityType())
      ->load($this->bundle());
  }

  /**
   * {@inheritDoc}
   */
  public function getConditions() {
    $value = $this->get('conditions')->getValue();
    return reset($value);
  }

  /**
   * Get conditions configured for this entity.
   *
   * @return array
   *   An array of configured condition plugin definitions.
   */
  public function getConfiguredConditions() {
    return array_filter($this->getConditions(), function (array $info) {
      if (!isset($info['configuration'])) {
        return FALSE;
      }
      if (!$this->conditionHasConfiguration($info['configuration'])) {
        return FALSE;
      }
      return $info;
    });
  }

  /**
   * {@inheritDoc}
   */
  public function hasConditions() {
    if ($this->getBundleEntityTypeEntity()->getAllowCondition()) {
      foreach ($this->getConditions() as $condition) {
        if ($this->conditionHasConfiguration($condition['configuration'] ?? [])) {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function conditionsRequired() {
    return (bool) $this->get('conditions_required')->value;
  }

  /**
   * Attaches instantiated condition plugins to this entity.
   *
   * @param \Drupal\Core\Condition\ConditionInterface[] $conditions
   *   An array of conditions, keyed by ID.
   */
  public function attachConditions(array $conditions) {
    foreach ($conditions as $id => $condition) {
      $this->addCacheableDependency($condition);
      $this->loadedConditions[$id] = $condition;
    }
  }

  /**
   * {@inheritDoc}
   */
  public function evaluateConditions(array $contexts = []) {
    $verdicts = [];

    foreach ($this->loadedConditions as $instance) {
      $verdicts[] = $this->computeConditionInstance($instance, $contexts);
    }

    if (empty($verdicts)) {
      return TRUE;
    }
    $verdicts = array_unique($verdicts);

    if (count($verdicts) === 1) {
      return (bool) reset($verdicts);
    }

    return !$this->conditionsRequired() && in_array(TRUE, $verdicts);
  }

  /**
   * Get the instance required contexts.
   *
   * @param \Drupal\Component\Plugin\ContextAwarePluginInterface $instance
   *   An plugin instance that supports context.
   *
   * @return array
   *   An array of required contexts.
   */
  protected function getRequiredContexts(ContextAwarePluginInterface $instance) {
    $contexts = [];

    foreach ($instance->getContextDefinitions() as $name => $definition) {
      if (!$definition->isRequired()) {
        continue;
      }
      $contexts[$definition->getDataType()] = $definition->getLabel();
    }

    return $contexts;
  }

  /**
   * Extract the required contexts.
   *
   * @param array $contexts
   *   An array of contexts.
   * @param array $required_contexts
   *   An array of required contexts.
   *
   * @return array
   *   An array of required contexts
   */
  protected function extractRequiredContexts(
    array $contexts,
    array $required_contexts
  ) {
    $contextsDataType = [];

    foreach ($contexts as $context) {
      $contextsDataType[$context->getContextDefinition()->getDataType()] = $context;
    }

    return array_filter(
      array_intersect_key($contextsDataType, $required_contexts)
    );
  }

  /**
   * Compute the condition instance.
   *
   * @param \Drupal\Core\Condition\ConditionInterface $instance
   *   The condition plugin instance.
   * @param array $contexts
   *   An array of contexts.
   *
   * @return bool
   *   Return the condition evaluation verdict; otherwise FALSE.
   */
  protected function computeConditionInstance(
    ConditionInterface $instance,
    array $contexts = []
  ) {
    if ($required_contexts = $this->getRequiredContexts($instance)) {
      $instance_contexts = $this->extractRequiredContexts(
        $contexts, $required_contexts
      );

      if (empty($instance_contexts)) {
        return FALSE;
      }

      foreach ($instance_contexts as $name => $context) {
        if (!$context->hasContextValue()) {
          return FALSE;
        }
        $contextNameParts = explode(':', $name);
        $contextId = array_pop($contextNameParts);
        $instance->setContext($contextId, $context);
      }
    }
    $verdict = $instance->evaluate();

    return $instance->isNegated() ? !$verdict : $verdict;
  }

  /**
   * Condition has configuration.
   *
   * @param array $configuration
   *   An array of condition configuration values.
   *
   * @return bool
   *   Return TRUE if the configuration have values; otherwise FALSE.
   */
  protected function conditionHasConfiguration(array $configuration) {
    return (bool) !empty(array_filter($configuration, function ($value, $key) {
      if (in_array($key, ['id', 'negate'])) {
        return FALSE;
      }
      return !empty($value);
    }, ARRAY_FILTER_USE_BOTH));
  }

  /**
   * Get message publish end date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   The published end date object.
   */
  protected function getPublishEndDate() {
    $date = $this->get('publish_end_date')->date;
    assert($date instanceof DrupalDateTime);
    return $date;
  }

  /**
   * Get message publish start date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   The published start date object.
   */
  protected function getPublishStartDate() {
    $date = $this->get('publish_start_date')->date;
    assert($date instanceof DrupalDateTime);
    return $date;
  }

  /**
   * Get the entity view builder.
   *
   * @return \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected function entityViewBuilder() {
    return $this->entityTypeManager()->getViewBuilder($this->getEntityTypeId());
  }

}
