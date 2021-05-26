<?php


namespace Drupal\buddy_profile_wizard\Controller;


use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;

class ProfileWizardController extends ControllerBase
{


  public function access(AccountInterface $account,NodeInterface $atEntry=NULL,NodeInterface $description=NULL,NodeInterface $type=NULL) {



    return AccessResult::allowed();
  }



}
