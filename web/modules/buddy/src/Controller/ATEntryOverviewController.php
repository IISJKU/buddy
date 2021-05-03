<?php
namespace Drupal\buddy\Controller;

use Drupal\buddy\Util\Util;
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
    $atPlatformsOfATEntries = [];
    foreach ($atEntries as $atEntry){


      $atDescriptionIDs = $atEntry->field_at_descriptions->getValue();
      $atDescriptions = Util::loadNodesByReferences($atDescriptionIDs);
      $atDescriptionsOfATEntries[$atEntry->id()] = $atDescriptions;

      $atPlatformIDs = $atEntry->field_at_types->getValue();
      $atPlatforms = Util::loadNodesByReferences($atPlatformIDs);
      $atPlatformsOfATEntries[$atEntry->id()] = $atPlatforms;

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
