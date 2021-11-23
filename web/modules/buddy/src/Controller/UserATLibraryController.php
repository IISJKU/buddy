<?php

namespace Drupal\buddy\Controller;

use Drupal\buddy\Util\Util;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

class UserATLibraryController extends ControllerBase
{


  public function content()
  {
    $title = $this->t("Tool library");
    $user = \Drupal::currentUser();

    //Get AT Entry of description
    $atRecordsIDs = \Drupal::entityQuery('node')
      ->condition('type', 'user_at_record')
      ->condition('field_user_at_record_library', true, '=')
      ->condition('uid', $user->id(), '=')
      ->execute();

    if(count($atRecordsIDs) == 0){

      return [
        '#type' => 'markup',
        '#markup' => '<div>'.$this->t("Your library is currently empty.").' </div><div>'
          .$this->t("Use the search function or catalogue to add tools to your library."). '</div>',
        '#title' => $title,

      ];
    }




    $html = '<div>'.$this->t("Here is your current library of tools.").'</div>';


    $atRecords = \Drupal::entityTypeManager()->getStorage('node')
      ->loadMultiple($atRecordsIDs);

    foreach ($atRecords as $key => $atRecord){

      $atEntryID = $atRecord->get("field_user_at_record_at_entry")->getValue()[0]['target_id'];


      $atEntry = Node::load($atEntryID);

      $descriptions = Util::getDescriptionsOfATEntry($atEntryID);
      $user = \Drupal::currentUser();

      $description = Util::getDescriptionForUser($descriptions,$user);
      $languages = Util::getLanguagesOfDescriptions($descriptions);
      $platforms = Util::getPlatformsOfATEntry($atEntry);
      $content = Util::renderDescriptionTiles($description,$user,$languages,$platforms,false,false);


      $html.="<div class='at_library_container'>";
      $html.=$content;
      $html.="<h4>".$this->t("Actions")."</h4><ul>";
      $html.= "<li>".Link::createFromRoute($this->t('Install Instructions'),'buddy.user_at_install_form',['description' => $description->id()],  ['attributes' => ['class' => 'btn btn-primary overview-button']])->toString()->getGeneratedLink()."</li>";
      $html.= "<li>".Link::createFromRoute($this->t('Remove'),'buddy.user_at_library_remove',['record' =>$atRecord->id()],  ['attributes' => ['class' => 'btn btn-primary overview-button']])->toString()->getGeneratedLink()."</li>";
      $html.="</ul>";

      $html .= UserATLibraryController::rating_widget_html();

      $html .= "</div>";

    }

    $build = array(
      '#type' => 'markup',
      '#markup' => $html,
      '#title' => $title,
      '#attached' => [
        'library' => [
          'buddy/user_at_library',
        ],
      ],
    );

    return $build;

    }

    public static function rating_widget_html() {
      $html = "<section class='rating-widget'>";

      $html .= "<div class='rating-stars text-center'>";
      $html .= "<ul id='stars'>";
      $html .= "<li class='star' title='Poor' data-value='1'>";
      $html .= "<i class='fa fa-star fa-fw'></i></li>";
      $html .= "<li class='star' title='Fair' data-value='2'>";
      $html .= "<i class='fa fa-star fa-fw'></i></li>";
      $html .= "<li class='star' title='Good' data-value='3'>";
      $html .= "<i class='fa fa-star fa-fw'></i></li>";
      $html .= "<li class='star' title='Very Good' data-value='4'>";
      $html .= "<i class='fa fa-star fa-fw'></i></li>";
      $html .= "<li class='star' title='Excellent' data-value='5'>";
      $html .= "<i class='fa fa-star fa-fw'></i></li>";
      $html .= "</ul></div>";

      $html .= "<div class='message-box'>";
      $html .= "<div class='clearfix'></div>";
      $html .= "<div class='text-message'></div>";
      $html .= "<div class='clearfix'></div>";
      $html .= "</div>";
      $html .= "";

      return $html;
    }


  public function removeATEntryFromLibrary() {
    $recordID = \Drupal::request()->query->get('record');

    if(is_numeric ($recordID)){
      $user = \Drupal::currentUser();
      $atRecordsIDs = \Drupal::entityQuery('node')
        ->condition('type', 'user_at_record')
        ->condition('field_user_at_record_library', true, '=')
        ->condition('uid', $user->id(), '=')
        ->condition('nid', $recordID, '=')
        ->execute();
      if(count($atRecordsIDs) > 0){

        $atRecords = \Drupal::entityTypeManager()->getStorage('node')
          ->loadMultiple($atRecordsIDs);


        $atRecord = reset($atRecords);
        $atRecord->field_user_at_record_library = ["value" => false];
        $atRecord->save();

        return $this->redirect('buddy.user_at_library');
      }
    }





    return $this->redirect('<front>');


  }


}
