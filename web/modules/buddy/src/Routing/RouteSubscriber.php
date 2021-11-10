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
    // Change user login routes to buddy user login and user register

    $route = $collection->get('user.login');
    if ($route) {
      $buddyLoginRoute = Url::fromRoute('buddy.user_login');
      $route->setPath($buddyLoginRoute->toString());

    }else{
      $route = $collection->get('user.register');
      if ($route) {
        $buddyRegisterRoute = Url::fromRoute('buddy.user_register');
        $route->setPath($buddyRegisterRoute->toString());
      }
    }



  }

}
