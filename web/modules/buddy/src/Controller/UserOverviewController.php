<?php

namespace Drupal\buddy\Controller;

use Drupal\buddy\Util\Util;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;

class UserOverviewController extends ControllerBase
{


  public function content()
  {

    $build = array(
      '#type' => 'markup',
      '#markup' => "MUH!",
      '#title' => $this->t("My Assistive Technology Entries"),
      '#attached' => [
        'library' => [
          'buddy/at_provider_forms',
        ],
      ],
    );

    return $build;

    }


}
