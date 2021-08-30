<?php

namespace Drupal\buddy\Controller;

use Drupal\buddy\Util\Util;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;

class UserOverviewController extends ControllerBase
{


  public function content()
  {


    $user = \Drupal::currentUser();

    $title = $this->t("Welcome ").$user->getAccountName();

    $html = "<h2>".$this->t("Assistive technology")."</h2>";
    $html.= Link::createFromRoute($this->t('My recommendations'),'buddy.user_at_recommendation',[],  ['attributes' => ['class' => 'btn btn-primary overview-button']])->toString()->getGeneratedLink();
    $html.= "<hr>";

    $html.= "<h2>".$this->t("My AT Library")."</h2><hr>";
    $html.= Link::createFromRoute($this->t('Goto my library'),'buddy.user_at_library',[],  ['attributes' => ['class' => 'btn btn-primary overview-button']])->toString()->getGeneratedLink();



    $html.= "<h2>".$this->t("Preferences")."</h2>";
    $html.= Link::createFromRoute($this->t('Adjust my preferences'),'buddy.user_profile',[],  ['attributes' => ['class' => 'btn btn-primary overview-button']])->toString()->getGeneratedLink();
    $html.= "<hr>";

    $build = array(
      '#type' => 'markup',
      '#markup' => $html,
      '#title' => $title,
    );

    return $build;

    }


}
