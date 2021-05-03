<?php


namespace Drupal\buddy\Controller;


use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;

class ATProviderController extends ControllerBase
{

  public static function hasAccess($node){

    $current_user = \Drupal::currentUser();

    if($node->getOwnerId() == $current_user->id()){
      return true;
    }

    if(in_array("administrator", $current_user->getRoles())){
      return true;
    }
    return false;

  }

  public function access(AccountInterface $account) {
    if(in_array("administrator", $account->getRoles()) || in_array("at_provider", $account->getRoles())){
      return AccessResult::allowed();
    }


    return AccessResult::forbidden();
  }



}
