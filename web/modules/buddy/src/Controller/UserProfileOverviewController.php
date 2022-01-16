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

    $title = $this->t("Profile for ").$user->getAccountName();

    $html = "<p>".$this->t("Here you can update your preferences and your account information.")."</p>";
    $html.="<div class='profile_container'><h3>".$this->t("Profile preferences")."</h3>";
    $html.="<p>".$this->t("If you keep your profile updated, Buddy can recommend you the most suitable tools.")."</p>";
    $html.="<p>".$this->t("This way, you do not have to look for tools by themselves by searching for specific tools.")."</p>";


    $user = \Drupal::currentUser();
    $user_profileID = \Drupal::entityQuery('node')
      ->condition('type', 'user_profile')
      ->condition('uid', $user->id(), '=')
      ->condition('field_user_profile_finished', true, '=')
      ->execute();
    if (count($user_profileID) == 1) {

      $html.= Link::createFromRoute($this->t('Update preferences'),'buddy.user_profile',[],  ['attributes' => ['class' => 'buddy_link_button buddy_button']])->toString()->getGeneratedLink();
    }else{
      $html.= Link::createFromRoute($this->t('Set preferences'),'buddy.user_profile',[],  ['attributes' => ['class' => 'buddy_link_button buddy_button']])->toString()->getGeneratedLink();
    }
    $html.= "</div>";
    $html.="<div class='profile_container'><h3>".$this->t("Account information")."</h3><p>".$this->t("Here you can:")."</p>";
    $html.= "<ul><li>".$this->t("Update your password")."</li>";
    $html.= "<li>".$this->t("Change email address")."</li>";
    $html.= "<li>".$this->t("Change language")."</li>";
    $html.= "<li>".$this->t("Delete your account")."</li></ul>";
    $html.= Link::createFromRoute($this->t('Adjust account information'),'buddy.user_account_form',[],  ['attributes' => ['class' => 'buddy_link_button buddy_button']])->toString()->getGeneratedLink();
    $html.= "</div>";



    $build = array(
      '#type' => 'markup',
      '#markup' => $html,
      '#title' => $title,
    );

    return $build;

    }


}
