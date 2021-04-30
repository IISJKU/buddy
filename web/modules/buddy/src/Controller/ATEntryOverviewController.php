<?php
namespace Drupal\buddy\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;

class ATEntryOverviewController extends ControllerBase
{


  public function ATEntryOverview(){

    $user = \Drupal::currentUser();


    $atEntriesID = \Drupal::entityQuery('node')
      ->condition('type', 'at_entry')
      ->condition('uid', $user->id(), '=')
      ->execute();

    $atEntries = \Drupal::entityTypeManager()->getStorage('node')
      ->loadMultiple($atEntriesID);


    $atDescriptionsOfATEntries = [];
    foreach ($atEntries as $atEntry){



      $atDescriptionIDs = \Drupal::entityQuery('node')
        ->condition('type', 'at_description')
        ->condition('field_at_entry', $atEntry->id(), '=')
        ->execute();


      $atDescriptions = \Drupal::entityTypeManager()->getStorage('node')
        ->loadMultiple($atDescriptionIDs);

      $id = $atEntry->id();
      $atDescriptionsOfATEntries[$atEntry->id()] = $atDescriptions;
    }


    $html = "<table><tr>
    <th>Entry</th>
    <th>Languages</th>
    <th>Manage Languages</th>
    <th>Platforms</th>
    <th>Manage Platforms</th>
      <th>Edit</th>
</tr>";



    $html.="</table>";


    $build = array(
      '#type' => 'markup',
      '#markup' => $html,
      '#title' => "AT Descriptions for ",
    );

    return $build;

  }

  private function renderATEntry($atEntry,$atDescriptions,$atPlatforms){


  }


  public function access(AccountInterface $account) {



    if(in_array("administrator", $account->getRoles()) || in_array("at_provider", $account->getRoles())){
      return AccessResult::allowed();
    }


    return AccessResult::forbidden();


  }

}
