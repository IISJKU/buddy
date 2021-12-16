<?php

namespace Drupal\buddy\Controller;

use Drupal\buddy\Util\Util;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
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

      $html = '<p class="mobile_intro_text">'.$this->t('<span class="buddy_main_page_intro_line">The assistive technology</span><span class="buddy_main_page_intro_line"> platform - that finds tools</span><span class="buddy_main_page_intro_line"> that work for you.</span>').'</p>
               <p class="desktop_intro_text">'.$this->t('<span class="buddy_main_page_intro_line">The assistive technology platform</span><span class="buddy_main_page_intro_line">  - that finds tools that work for you.</span>').'</p>
               <ul class="buddy_login_menu">
	                  <li>'.$createUserLink.'</li>
	                  <li>'.$loginLink.'</li>
               </ul>';

      $build = array(
        '#type' => 'markup',
        '#markup' => $html,
        '#title' => $this->t("Welcome to Buddy!"),
        '#attached' => ['library'=> ['buddy/main_page']] ,
      );
      return $build;
    }else{


      $recommendationURL = Url::fromRoute('buddy.user_search')->toString();
      $searchURL = Url::fromRoute('buddy.user_at_recommendation')->toString();
      $manageToolsURL = Url::fromRoute('buddy.user_at_library')->toString();


      $html = '<p >'.$this->t('With Buddy, you can:').'</p>
               <ul class="buddy_user_main_menu">';

      $html .= '<li>';
      $html .= '<a class="buddy_link_button buddy_button" href="'.$recommendationURL.'">';
      $html .= '<i class="fas fa-robot"></i>';
      $html .= $this->t('Find a tool for you');
      $html .= '</a></li>';

      $html .= '<li>';
      $html .= '<a class="buddy_link_button buddy_button" href="'.$searchURL.'">';
      $html .= '<i class="fas fa-search"></i>';
      $html .= $this->t('Search for tools');
      $html .= '</a></li>';

      $user = \Drupal::currentUser();
      //Get AT Entry of description
      $atRecordsIDs = \Drupal::entityQuery('node')
        ->condition('type', 'user_at_record')
        ->condition('field_user_at_record_library', true, '=')
        ->condition('uid', $user->id(), '=')
        ->execute();

      if(count($atRecordsIDs) != 0) {
        $html .= '<li>';
        $html .= '<a class="buddy_link_button buddy_button" href="'.$manageToolsURL.'">';
        $html .= '<i class="fas fa-tools"></i>';
        $html .= $this->t('Rate your tools');
        $html .= '</a></li>';
      }

      $html.="</ul>";



      $build = array(
        '#type' => 'markup',
        '#markup' => $html,
        '#title' => $this->t("Welcome")." ".$user->getAccountName(),
        '#attached' => ['library'=> ['buddy/main_page']] ,
      );

      return $build;
    }




  }

  public function access(AccountInterface $account, NodeInterface $atEntry = NULL, NodeInterface $description = NULL, NodeInterface $type = NULL)
  {

    return AccessResult::allowed();
  }


}
