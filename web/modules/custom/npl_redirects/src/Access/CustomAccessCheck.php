<?php
    namespace Drupal\npl_redirects\Access;

    use Drupal\Core\Session\AccountInterface;
    use Drupal\Core\Access\AccessResult;
    use Drupal\Core\Routing\Access\AccessInterface;

    /**
     * Checks access for displaying configuration translation page.
     */
    class CustomAccessCheck implements AccessInterface {

        /**
         * A custom access check.
         *
         * @param \Drupal\Core\Session\AccountInterface $account
         *   Run access checks for this account.
         *
         * @return \Drupal\Core\Access\AccessResultInterface
         *   The access result.
         */
        public function access(AccountInterface $account) {
            // Check permissions and combine that with any custom access checking needed. Pass forward
            // parameters from the route and/or request as needed.
            return ($account->hasPermission('add npl_redirects')) ? AccessResult::allowed() : AccessResult::forbidden();
        }

        public function access_edit(AccountInterface $account) {
            // Check permissions and combine that with any custom access checking needed. Pass forward
            // parameters from the route and/or request as needed.
            return ($account->hasPermission('edit npl_redirects')) ? AccessResult::allowed() : AccessResult::forbidden();
        }

        public function access_delete(AccountInterface $account) {
            // Check permissions and combine that with any custom access checking needed. Pass forward
            // parameters from the route and/or request as needed.
            return ($account->hasPermission('delete npl_redirects')) ? AccessResult::allowed() : AccessResult::forbidden();
        }

        public function access_view(AccountInterface $account) {
            // Check permissions and combine that with any custom access checking needed. Pass forward
            // parameters from the route and/or request as needed.
            return ($account->hasPermission('view npl_redirects')) ? AccessResult::allowed() : AccessResult::forbidden();
        }
    }