<?php


namespace Drupal\buddy\Controller;


use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;

class ATProviderController extends ControllerBase
{


  public function access(AccountInterface $account,NodeInterface $atEntry=NULL,NodeInterface $description=NULL,NodeInterface $type=NULL) {

    $node = null;
    if($atEntry){
      $node =$atEntry;
    }else if($description){
      $node =$description;
    }else if($type){
      $node =$type;
    }
/*
    if(in_array("administrator", $account->getRoles())){
      return AccessResult::allowed();
    }*/

    if(in_array("administrator", $account->getRoles())){

      if($node){
        if($node->getOwnerId() == $account->id()){
          return AccessResult::allowed();
        }
      }else{
        return AccessResult::allowed();
      }

    }

    return AccessResult::forbidden();
  }



}
