<?php

namespace Drupal\buddy\Controller;

use Drupal\buddy\Util\Util;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;

class UserFrontPageController extends ControllerBase
{


  public function content()
  {


    $user = \Drupal::currentUser();
    $logged_in = \Drupal::currentUser()->isAuthenticated();


    if(!$logged_in){
      $createUserLink = Link::createFromRoute($this->t('Create account'),'buddy.user_register',[],['attributes' => ['class' => 'buddy_link_button create_account_button']])->toString()->getGeneratedLink();
      $loginLink = Link::createFromRoute($this->t('Log in'),'buddy.user_login',[],['attributes' => ['class' => 'buddy_link_button login_button']])->toString()->getGeneratedLink();

      $html = '<p>'.$this->t('The assistive technology platform - that finds tools that work for you.').'</p>
               <ul class="buddy_login_menu">
	                  <li>'.$createUserLink.'</li>
	                  <li>'.$loginLink.'</li>
               </ul>';

      $build = array(
        '#type' => 'markup',
        '#markup' => $html,
        '#title' => $this->t("Welcome to Buddy!"),
      );

      return $build;
    }else{

      $createUserLink = Link::createFromRoute($this->t('Create account'),'buddy.user_register',[],['attributes' => ['class' => 'buddy_link_button create_account_button']])->toString()->getGeneratedLink();
      $loginLink = Link::createFromRoute($this->t('Log in'),'buddy.user_login',[],['attributes' => ['class' => 'buddy_link_button login_button']])->toString()->getGeneratedLink();


      $html = $this->t("Todo: Add information about what users can do here..");

      $build = array(
        '#type' => 'markup',
        '#markup' => $html,
        '#title' => $this->t("Welcome ".$user->getAccountName()),
      );

      return $build;
    }




  }

  public function access(AccountInterface $account, NodeInterface $atEntry = NULL, NodeInterface $description = NULL, NodeInterface $type = NULL)
  {

    return AccessResult::allowed();
  }


}
