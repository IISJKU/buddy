<?php

namespace Drupal\buddy\Controller;

use Drupal\buddy\Util\Util;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;

class ATModeratorATEntriesOverviewController extends ControllerBase
{


  public function ATEntryOverview()
  {

    $atEntriesID = \Drupal::entityQuery('node')
      ->condition('type', 'at_entry')
      ->execute();

    $atEntries = \Drupal::entityTypeManager()->getStorage('node')
      ->loadMultiple($atEntriesID);


    $atDescriptionsOfATEntries = [];
    $atPlatformsOfATEntries = [];
    $authors = [];
    foreach ($atEntries as $atEntry) {
      $atDescriptionIDs = $atEntry->field_at_descriptions->getValue();
      $atDescriptions = Util::loadNodesByReferences($atDescriptionIDs);
      $atDescriptionsOfATEntries[$atEntry->id()] = $atDescriptions;

      $atPlatformIDs = $atEntry->field_at_types->getValue();
      $atPlatforms = Util::loadNodesByReferences($atPlatformIDs);
      $atPlatformsOfATEntries[$atEntry->id()] = $atPlatforms;

      $authors[$atEntry->id()]  = User::load($atEntry->getOwnerId());

    }

    $addToolURL = Url::fromRoute('buddy.at_moderator_at_entry_form')->toString();
    $html = '<div class="at_entry_header_menu">
        <a href="'.$addToolURL.'">'.$this->t("Add new tool").'
                    <i class="fa fa-plus" aria-hidden="true"></i> </a>
             </div>';
    $html.= '<table class="table buddy_at_table  table-striped"><tr>
                <th scope="row">'.$this->t("Name").'</th>
                <th scope="row">'.$this->t("Author").'</th>
                <th scope="row">'.$this->t("Languages").'</th>
                <th scope="row">'.$this->t("Platforms").'</th>
                <th scope="row">'.$this->t("Edit").'</th>
</tr>';

    foreach ($atEntries as $atEntry){

      $html.= "<tr>";

      $html.= "<td>".$atEntry->getTitle()."</td>";

      $author = $authors[$atEntry->id()];
      $name = $author->get('name')->value;
      $html.= "<td>".$name."</td>";

      $descriptions = $atDescriptionsOfATEntries[$atEntry->id()];
      $descriptionHTML = "";
      $firstTime = true;
      foreach ($descriptions as $description){

        if(!$firstTime){
          $descriptionHTML.=", ";
        }
        $lang = $description->field_at_description_language->getValue();
        $descriptionHTML.=$lang[0]['value'];
        $firstTime = false;
      }
      $html.= "<td>".$descriptionHTML."</td>";

      $platforms = $atPlatformsOfATEntries[$atEntry->id()];


      $html.= "<td>".Util::renderPlatformOverview($platforms)."</td>";
      $editURL = Link::createFromRoute($this->t('Edit'),'buddy.at_moderator_at_entry_overview',['atEntry' => $atEntry->id()])->toString()->getGeneratedLink();

      $html.= "<td>".$editURL."</td>";

      $html.="</tr>";

    }

    $html.="</table>";

     $build = array(
      '#type' => 'markup',
      '#markup' => $html,
      '#title' => $this->t("Manage Tools"),
      '#attached' => [
        'library' => [
          'buddy/at_provider_forms',
        ],
      ],
    );

    return $build;

  }

  private function renderATEntry($atEntry, $atDescriptions, $atTypes)
  {
    $html = '<div class="at_entry_container">

';


    //Descriptions
    $html .= '
    <div class="at_entry_descriptions">
        <div class="at_container_header">
            <h3>Descriptions</h3>
            <a href="create-description/' . $atEntry->id() . '">Add Description <i class="fa fa-plus" aria-hidden="true"></i> </a>
    </div>

    <div class="at_container_table">
              <table class="table table-hover table-responsive ">
          <tr>
              <th scope="col">Title</th>
              <th scope="col">Language</th>
               <th scope="col">Published</th>
              <th scope="col">Draft</th>
              <th scope="col">Delete</th>
          </tr>';

    foreach ($atDescriptions as $atDescription) {
      $revision_ids = \Drupal::entityTypeManager()->getStorage('node')->revisionIds($atDescription);
      $last_revision_id = end($revision_ids);

      $draftTitle = "";
      if ($atDescription->getRevisionId() != $last_revision_id) {
        $draftTitle= $this->t("Edit revision");
      }else{
        $draftTitle = $this->t("Create new revision");
      }
      $mod = $atDescription->get('moderation_state')->getValue();
      $published = "-";
      if($mod[0]['value'] == 'draft'){
        $draftTitle= $this->t("Edit draft");
      }else{

        $published = '<a href="view-description/'.$atDescription->id().'">'.$this->t("View published").'</a>';
      }
      $lang = $atDescription->field_at_description_language->getValue();
      $html .= '<tr>
                <td>'.$atDescription->getTitle().'</td>
                <td>' . $lang[0]['value'] . '</td>
                <td>'.$published.'</td>
                <td><a href="edit-description/' . $atDescription->id() . '">' .$draftTitle . '</a></td>
                <td><a href="delete-description/' . $atDescription->id() . '" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i><span class="sr-only">Delete</span></a></td>
              </tr>';

    }

    $html .= '</table>
    </div>

</div>';

    //Types

    $html .= '<div class="at_entry_types">
    <div class="at_container_header">
        <h3>Types</h3>
        <a href="create-type/' . $atEntry->id() . '">Add Type <i class="fa fa-plus" aria-hidden="true"></i></a>
    </div>
    <div class="at_container_table">
        <table class="table table-hover table-responsive ">
            <tr>
              <th scope="col">Type</th>
              <th scope="col">Platform</th>
              <th class="type_edit_header" scope="col">Edit</th>
              <th class="type_delete_header" scope="col">Delete</th>
            </tr>';
    foreach ($atTypes as $atType) {
      $html.="<tr>";

      $type = $atType->bundle();
      if ($type == "at_type_software") {
        $html.= "<td>".$this->t("Software").'</td>';

        $os = Node::load( $atType->get("field_type_software_os")->getValue()[0]['target_id']);
        $html.= "<td>".$os->getTitle().'</td>';


      } else if ($type == "at_type_app") {
        $html.= "<td>".$this->t("App").'</td>';
        $os = Node::load($atType->get("field_app_os")->getValue()[0]['target_id']);
        $html.= "<td>".$os->getTitle().'</td>';
      } else {
        //browser_extension
        $html.= "<td>".$this->t("Browser Extension").'</td>';


        $browser = Node::load($atType->get("field_type_browser")->getValue()[0]['target_id']);
        $html.= "<td>".$browser->getTitle().'</td>';

      }



      $html.= '<td><a href="edit-type/' . $atType->id() . '" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i><span class="sr-only">Edit</span></a></td>';
      $html.= '<td><a href="delete-type/' . $atType->id() . '" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i><span class="sr-only">Delete</span></a></td>';
      $html.="</tr>";


    }

    $html .= '
        </table>
    </div>

</div>
</div>';

    return $html;

    }


}
