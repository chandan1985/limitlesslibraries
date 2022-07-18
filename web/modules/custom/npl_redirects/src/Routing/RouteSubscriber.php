<?php

    namespace Drupal\npl_redirects\Routing;

    use Drupal\Core\Routing\RouteSubscriberBase;
    use Symfony\Component\Routing\RouteCollection;

    /**
     * Listens to the dynamic route events.
     */
    class RouteSubscriber extends RouteSubscriberBase {

        /**
         * {@inheritdoc}
         */
        protected function alterRoutes(RouteCollection $collection) {
            if ($route = $collection->get('npl_redirects.npl_redirects')) {
            $route->setRequirement('_custom_access', 'npl_redirects.access_checker::access');
            }
            if ($route = $collection->get('npl_redirects.createnpl_redirects')) {
                $route->setRequirement('_custom_access', 'npl_redirects.access_checker::access_edit');
            }
            if ($route = $collection->get('npl_redirects.deletenpl_redirects')) {
                $route->setRequirement('_custom_access', 'npl_redirects.access_checker::access_delete');
            }
            if ($route = $collection->get('npl_redirects.getnpl_redirectsList')) {
                $route->setRequirement('_custom_access', 'npl_redirects.access_checker::access_view');
            }
        }
    }