<?php

namespace Drupal\notification_message\Entity;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Plugin\Context\LazyContextRepository;
use Drupal\Core\Session\AccountInterface;

/**
 * Define the notification message access control handler.
 */
class NotificationMessageAccess extends EntityAccessControlHandler {

  /**
   * {@inheritDoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    $route_name = $this->currentRouteMatch()->getRouteName();
    $admin_permission = $entity->getEntityType()->getAdminPermission();

    if ($account->hasPermission($admin_permission)) {
      $admin_access = AccessResult::allowedIfHasPermission($account, $admin_permission);
      if (
        $operation === 'view'
        && $route_name !== 'entity.notification_message.canonical'
      ) {
        return $admin_access->andIf(
          $this->evaluateVisibilityConditions($entity, $account)
        );
      }
      return $admin_access;
    }
    if ($operation === 'view') {
      assert($entity instanceof EntityPublishedInterface);
      $result = AccessResult::allowedIf($entity->isPublished())->addCacheableDependency($entity);
      if (!$entity->isPublished()) {
        if ($entity->getAuthorUser()->id() === $account->id()) {
          $result = $result->orIf(AccessResult::allowedIfHasPermission($account, 'view own unpublished notification message'));
        }
        $result->orIf(AccessResult::allowedIfHasPermission($account, 'view any unpublished notification message'));
      }
      if ($route_name !== 'entity.notification_message.canonical' && $result->isAllowed()) {
        $result = $result->andIf($this->evaluateVisibilityConditions($entity, $account));
      }
      return $result;
    }
    return AccessResult::neutral();
  }

  /**
   * Evaluates whether the message should be displayed.
   */
  protected function evaluateVisibilityConditions(EntityInterface $entity, AccountInterface $account) {
    return AccessResult::allowedIf(
      $entity->evaluateConditions($this->loadContexts())
    )->addCacheableDependency($entity);
  }

  /**
   * Loads and returns condition contexts.
   *
   * @return \Drupal\Core\Plugin\Context\ContextInterface[]
   *   An array of loaded contexts, keyed by an unqualified context ID.
   */
  protected function loadContexts() {
    $context_repository = \Drupal::service('context.repository');
    assert($context_repository instanceof LazyContextRepository);

    $context_ids = array_keys($context_repository->getAvailableContexts());
    $contexts = [];

    foreach ($context_repository->getRuntimeContexts($context_ids) as $name => $context) {
      $unqualified_context_id = substr($name, strpos($name, ':') + 1);
      $contexts[$unqualified_context_id] = $context;
    }

    return $contexts;
  }

  /**
   * Current route match service.
   *
   * @return \Drupal\Core\Routing\CurrentRouteMatch
   *   The current route match service.
   */
  protected function currentRouteMatch(): CurrentRouteMatch {
    return \Drupal::service('current_route_match');
  }

}
