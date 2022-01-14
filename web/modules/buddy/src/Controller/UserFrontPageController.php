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

    $leftColumnHTML="";
    $rightColumnHTML = "";
    $title = $this->t("Welcome to Buddy!");
    if(!$logged_in){
      $title = $this->t("Welcome to Buddy!");

      $createUserLink = Link::createFromRoute($this->t('Create account'),'buddy.user_register',[],['attributes' => ['class' => 'buddy_link_button create_account_button']])->toString()->getGeneratedLink();
      $loginLink = Link::createFromRoute($this->t('Log in'),'buddy.user_login',[],['attributes' => ['class' => 'buddy_link_button login_button']])->toString()->getGeneratedLink();

      $leftColumnHTML = '<p class="mobile_intro_text">'.$this->t('<span class="buddy_main_page_intro_line">The assistive technology platform </span><span class="buddy_main_page_intro_line">that finds tools</span><span class="buddy_main_page_intro_line"> that work for you.</span>').'</p>
               <p class="desktop_intro_text">'.$this->t('<span class="buddy_main_page_intro_line">The assistive technology platform </span><span class="buddy_main_page_intro_line">that finds tools that work for you.</span>').'</p>
               <ul class="buddy_login_menu">
	                  <li>'.$createUserLink.'</li>
	                  <li>'.$loginLink.'</li>
               </ul>';



      $rightColumnHTML = '<h2>'.$this->t("This is what Buddy can do for you").'</h2>';
      $rightColumnHTML.="<p>".$this->t("In the Buddy platform you can find assistive technology")."</p>";
      $rightColumnHTML.="<p>".$this->t("You can find a tool on your own, or let Buddy suggest one for you.")."</p>";
      $rightColumnHTML.="<p>".$this->t("If you want reading support on this platform you can use Easy Reading, located in the top right corner.")."</p>";

    }else{



      $title = $this->t("Welcome")." ".$user->getAccountName();

      $searchURL = Url::fromRoute('buddy.user_search')->toString();
      $recommendationURL = Url::fromRoute('buddy.user_at_recommendation')->toString();
      $manageToolsURL = Url::fromRoute('buddy.user_at_library')->toString();


      $leftColumnHTML = '<p >'.$this->t('With Buddy, you can:').'</p>
               <ul class="buddy_user_main_menu">';

      $leftColumnHTML .= '<li>';
      $leftColumnHTML .= '<a class="buddy_link_button buddy_button" href="'.$recommendationURL.'">';
      $leftColumnHTML .= '<i class="fas fa-robot"></i>';
      $leftColumnHTML .= $this->t('Find a tool for you');
      $leftColumnHTML .= '</a></li>';

      $leftColumnHTML .= '<li>';
      $leftColumnHTML .= '<a class="buddy_link_button buddy_button" href="'.$searchURL.'">';
      $leftColumnHTML .= '<i class="fas fa-search"></i>';
      $leftColumnHTML .= $this->t('Search for tools');
      $leftColumnHTML .= '</a></li>';

      $user = \Drupal::currentUser();
      //Get AT Entry of description
      $atRecordsIDs = \Drupal::entityQuery('node')
        ->condition('type', 'user_at_record')
        ->condition('field_user_at_record_library', true, '=')
        ->condition('uid', $user->id(), '=')
        ->execute();

      if(count($atRecordsIDs) != 0) {
        $leftColumnHTML .= '<li>';
        $leftColumnHTML .= '<a class="buddy_link_button buddy_button" href="'.$manageToolsURL.'">';
        $leftColumnHTML .= '<i class="fas fa-tools"></i>';
        $leftColumnHTML .= $this->t('Rate your tools');
        $leftColumnHTML .= '</a></li>';
      }

      $leftColumnHTML.="</ul>";


      $rightColumnHTML = '<h2>'.$this->t("This is what Buddy can do for you").'</h2>';
      $rightColumnHTML.="<p>".$this->t("In the Buddy platform you can find assistive technology")."</p>";
      $rightColumnHTML.="<p>".$this->t("You can find a tool on your own, or let Buddy suggest one for you.")."</p>";
      $rightColumnHTML.="<p>".$this->t("If you want reading support on this platform you can use Easy Reading, located in the top right corner.")."</p>";

    }


    $html = '<div class="row">
  <div class="col-12 col-lg-6">'.$leftColumnHTML.'</div>
  <div class="col-12 col-lg-6">'.$rightColumnHTML.'</div>
</div>';

    $build = array(
      '#type' => 'markup',
      '#markup' => $html,
      '#title' => $title,
      '#attached' => ['library'=> ['buddy/main_page']] ,
    );
    return $build;


  }

  public function access(AccountInterface $account, NodeInterface $atEntry = NULL, NodeInterface $description = NULL, NodeInterface $type = NULL)
  {

    return AccessResult::allowed();
  }


}
