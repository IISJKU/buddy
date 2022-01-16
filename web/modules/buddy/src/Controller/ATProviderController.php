<?php


namespace Drupal\buddy\Controller;


use Drupal\buddy\Util\Util;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

class ATProviderController extends ControllerBase
{


  public function access(AccountInterface $account,NodeInterface $atEntry=NULL,NodeInterface $description=NULL,NodeInterface $type=NULL) {

    $node = null;
    if($atEntry){
      $node =$atEntry;
    }else if($description){
      $atEntry = Node::load($description->field_at_entry->getValue()[0]['target_id']);
      $node =$atEntry;
    }else if($type){
      $atEntry = Node::load(Util::getATEntryIDOfType($type));
      $node =$atEntry;
    }

    if(in_array("administrator", $account->getRoles())){
      return AccessResult::allowed();
    }

    if(in_array("at_provider", $account->getRoles())){

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
