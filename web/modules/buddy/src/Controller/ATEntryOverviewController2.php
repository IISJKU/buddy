<?php

namespace Drupal\buddy\Controller;

use Drupal\buddy\Util\Util;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;

class ATEntryOverviewController2 extends ControllerBase
{


  public function ATEntryOverview()
  {

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
    foreach ($atEntries as $atEntry) {
      $atDescriptionIDs = $atEntry->field_at_descriptions->getValue();
      $atDescriptions = Util::loadNodesByReferences($atDescriptionIDs);
      $atDescriptionsOfATEntries[$atEntry->id()] = $atDescriptions;

      $atPlatformIDs = $atEntry->field_at_types->getValue();
      $atPlatforms = Util::loadNodesByReferences($atPlatformIDs);
      $atPlatformsOfATEntries[$atEntry->id()] = $atPlatforms;

    }

    $html = '<div class="at_entry_menu"> <a href="create-at-entry">'.$this->t("Add new assistive technology").'<i class="fa fa-plus" aria-hidden="true"></i> </a></div>';
    foreach ($atEntries as $atEntry) {

      $html .= $this->renderATEntry($atEntry, $atDescriptionsOfATEntries[$atEntry->id()], $atPlatformsOfATEntries[$atEntry->id()]);
    }

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

  private function renderATEntry($atEntry, $atDescriptions, $atTypes)
  {
    $html = '<div class="at_entry_container">
<div class="at_entry_header">
<h2>' . $atEntry->getTitle() . '</h2>
 <a href="delete-at-entry/' . $atEntry->id() . '" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i><span class="sr-only">Delete</span> </a>
 <a href="edit-at-entry/' . $atEntry->id() . '" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i><span class="sr-only">Edit</span></a>
 </div>
';


    //Descriptions
    $html .= '
    <div class="at_entry_descriptions">
        <div class="at_container_header">
            <h3>Descriptions</h3>
            <a href="create-description/' . $atEntry->id() . '">Add Description<i class="fa fa-plus" aria-hidden="true"></i> </a>
    </div>

    <div class="at_container_table">
              <table class="table table-hover table-responsive ">
          <tr class="table-primary">
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
            <tr class="table-primary">
              <th scope="col">Type</th>
              <th class="type_edit_header" scope="col">Edit</th>
              <th class="type_delete_header" scope="col">Delete</th>
            </tr>';
    foreach ($atTypes as $atType) {
      $html.="<tr>";

      $type = $atType->bundle();
      if ($type == "at_type_software") {
        $html.= "<td>".$this->t("Software").'</td>';
      } else if ($type == "at_type_app") {
        $html.= "<td>".$this->t("App").'</td>';
      } else {
        //browser_extension
        $html.= "<td>".$this->t("Browser Extension").'</td>';
      }


      $html.= '<td><a href="edit-type/' . $atType->id() . '" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i><span class="sr-only">Edit</span></a></td>';
      $html.= '<td><a href="delete-type/' . $atType->id() . '" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i><span class="sr-only">Delete</span></a></td>';
      $html.="</tr>";


    }

    $html .= '
        </table>
    </div>

</div>
</div></div>';

    return $html;

    }


}
