<?php

namespace Drupal\buddy\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Url;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Change user login routes to login wizard
    //TODO

    return;
    $route = $collection->get('user.login');
    if (!$route) {
      $route = $collection->get('user.register');
    }
    if ($route) {
      $wizard_path = Url::fromRoute('buddy.user_entry_point');
      $route->setPath($wizard_path->toString());
    }
  }

}
