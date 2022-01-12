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

    $html = "<p>".$this->t("You can change your preferences or adjust your account here.")."</p>";
    $html.="<div class='profile_container'><h3>".$this->t("Preferences")."</h3><p>".$this->t("If you are not happy with the tools that Buddy recommends you, you can update your preferences to tell Buddy what help you need.")."</p>";

    $html.= Link::createFromRoute($this->t('Update preferences'),'buddy.user_profile',[],  ['attributes' => ['class' => 'buddy_link_button buddy_button']])->toString()->getGeneratedLink();
    $html.= "</div>";
    $html.="<div class='profile_container'><h3>".$this->t("Account")."</h3><p>".$this->t("Adjusting your account allows you to:")."</p>";
    $html.= "<ul><li>".$this->t("Change your password")."</li>";
    $html.= "<li>".$this->t("Change your email address")."</li>";
    $html.= "<li>".$this->t("Change language")."</li>";
    $html.= "<li>".$this->t("Delete your account")."</li></ul>";
    $html.= Link::createFromRoute($this->t('Adjust account'),'buddy.user_account_form',[],  ['attributes' => ['class' => 'buddy_link_button buddy_button']])->toString()->getGeneratedLink();
    $html.= "</div>";



    $build = array(
      '#type' => 'markup',
      '#markup' => $html,
      '#title' => $title,
    );

    return $build;

    }


}
