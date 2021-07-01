<?php


namespace Drupal\buddy\Controller;


use Drupal\Core\Controller\ControllerBase;

class ATBackendOverviewController extends ControllerBase
{
  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */
  public function content() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Hello, World!'),
    ];
  }
}
