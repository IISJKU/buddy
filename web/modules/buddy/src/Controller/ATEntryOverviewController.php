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

    //throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();

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

    $html = '<a href="create-at-entry">'.$this->t("Create new assistive technology entry").'</a>';

    $html.= "<table><tr>
    <th scope='col'>Entry</th>
    <th scope='col'>Languages</th>
    <th scope='col'>Add Language</th>
    <th scope='col'>Types</th>
    <th scope='col'>Add Type</th>
    <th scope='col'>Edit</th>
</tr>";

    foreach ($atEntries as $atEntry){

      $html.=$this->renderATEntry($atEntry, $atDescriptionsOfATEntries[$atEntry->id()], $atPlatformsOfATEntries[$atEntry->id()] );
    }



    $html.="</table>";


    $build = array(
      '#type' => 'markup',
      '#markup' => $html,
      '#title' => $this->t("My Assistive Technology Entries"),
      '#attached' => [
        'library' => [
          'buddy/at_provider_forms',
        ],
      ],
    );

    return $build;

  }

  private function renderATEntry($atEntry, $atDescriptions, $atTypes){

    $entry = $atEntry->getTitle();

    $languages = "";
    foreach ($atDescriptions as $atDescription){
      $lang = $atDescription->field_at_description_language->getValue();

      if($languages != ""){
        $languages.=", ";
      }
      $languages.='<a href="edit-description/'.$atDescription->id().'">'.$lang[0]['value'].'</a>';
      $a = 1;
    }

    $manageLanguages = '<a href="create-description/'.$atEntry->id().'">Add language</a>';

    $types = "";
    foreach ($atTypes as $atType){
      if($types != ""){
        $types.=", ";
      }

      $type = $atType->bundle();

      if($type == "at_type_software"){
        $types.='<a href="edit-type/'.$atType->id().'">'.$this->t("Software").'</a>';
      }else if($type== "at_type_app"){
        $types.='<a href="edit-type/'.$atType->id().'">'.$this->t("App").'</a>';
      }else{
        //browser_extension
        $types.='<a href="edit-type/'.$atType->id().'">'.$this->t("Browser extension").'</a>';
      }


    }

    $manageTypes = '<a href="create-type/'.$atEntry->id().'">'.$this->t("Add type").'</a>';

    $editEntry = '<a href="edit-at-entry/'.$atEntry->id().'">'.$this->t("Edit/Delete").'</a>';


    return '<tr><td>'.$entry.'</td><td>'.$languages.'</td><td>'.$manageLanguages.'</td><td>'.$types.'</td><td>'.$manageTypes.'</td><td>'.$editEntry.'</td>';
  }




}
