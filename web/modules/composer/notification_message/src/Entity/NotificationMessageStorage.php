<?php

declare(strict_types=1);

namespace Drupal\notification_message\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Executable\ExecutableManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class NotificationMessageStorage extends SqlContentEntityStorage {

  /**
   * The condition plugin manager.
   *
   * This is used to instantiate each message's condition plugins after they are
   * loaded from the database.
   *
   * @var \Drupal\Core\Executable\ExecutableManagerInterface
   */
  protected $conditionManager;

  /**
   * @param \Drupal\Core\Executable\ExecutableManagerInterface $condition_manager
   */
  public function setConditionManager(ExecutableManagerInterface $condition_manager): void {
    $this->conditionManager = $condition_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    $storage = parent::createInstance($container, $entity_type);
    assert($storage instanceof self);
    $condition_manager = $container->get('plugin.manager.condition');
    assert($condition_manager instanceof ExecutableManagerInterface);
    $storage->setConditionManager($condition_manager);
    return $storage;
  }

  /**
   * {@inheritdoc}
   */
  protected function postLoad(array &$entities) {
    parent::postLoad($entities);
    // Every notification message may have one or more conditions that affect
    // whether it should be rendered or not. This instantiates those condition
    // plugins and attaches them to the entity.
    foreach ($entities as $entity) {
      assert($entity instanceof NotificationMessage);
      if ($entity->hasConditions()) {
        $loaded = [];
        foreach ($entity->getConfiguredConditions() as $id => $info) {
          $loaded[$id] = $this->conditionManager->createInstance($id, $info['configuration']);
        }
        $entity->attachConditions($loaded);
      }
    }
  }

}
