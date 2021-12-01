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

    $html = "<p>".$this->t("You can update your preferences or your account information here.")."</p>";
    $html.="<div class='profile_container'><h3>".$this->t("Updating your preferences allows you to:")."</h3>";
    $html.= "<ul><li>".$this->t("Tell Buddy where you need help.")."</li>";
    $html.= "<li>".$this->t("Tell Buddy which help you need.")."</li></ul>";
    $html.= Link::createFromRoute($this->t('Update preferences'),'buddy.user_profile',[],  ['attributes' => ['class' => 'buddy_link_button buddy_button']])->toString()->getGeneratedLink();
    $html.= "</div>";
    $html.="<div class='profile_container'><h3>".$this->t("Updating your account information allows you to:")."</h3>";
    $html.= "<ul><li>".$this->t("Update your password")."</li>";
    $html.= "<li>".$this->t("Change email address")."</li>";
    $html.= "<li>".$this->t("Change language")."</li></ul></div>";
    $html.= Link::createFromRoute($this->t('Update account information'),'buddy.user_account_form',[],  ['attributes' => ['class' => 'buddy_link_button buddy_button']])->toString()->getGeneratedLink();




    $build = array(
      '#type' => 'markup',
      '#markup' => $html,
      '#title' => $title,
    );

    return $build;

    }


}
