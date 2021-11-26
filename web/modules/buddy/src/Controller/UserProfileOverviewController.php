<?php

namespace Drupal\buddy\Controller;

use Drupal\buddy\Util\Util;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;

class UserProfileOverviewController extends ControllerBase
{


  public function content()
  {


    $user = \Drupal::currentUser();

    $title = $this->t("Welcome ").$user->getAccountName();

    $html = "<div>".$this->t("You can update your preferences or your account information here.")."</div>";
    $html.="<h3>".$this->t("Updating your preferences allows you to:")."</h3>";
    $html.= "<ul><li>".$this->t("Tell Buddy where you need help")."</li>";
    $html.= "<li>".$this->t("Updating your preferences allows you too:")."</li></ul>";
    $html.= Link::createFromRoute($this->t('Update preferences'),'buddy.user_profile',[],  ['attributes' => ['class' => 'buddy_link_button buddy_button']])->toString()->getGeneratedLink();
    $html.= "<br>";
    $html.= Link::createFromRoute($this->t('Update account information'),'buddy.user_account_form',[],  ['attributes' => ['class' => 'buddy_link_button buddy_button']])->toString()->getGeneratedLink();




    $build = array(
      '#type' => 'markup',
      '#markup' => $html,
      '#title' => $title,
    );

    return $build;

    }


}
