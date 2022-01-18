<?php

namespace Drupal\buddy\Controller;

use Drupal\buddy\Util\Util;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Display\Annotation\PageDisplayVariant;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

class UserATLibraryController extends ControllerBase
{


  public function content()
  {
    $title = $this->t("Favourites");
    $user = \Drupal::currentUser();

    //Get AT Entry of description
    $atRecordsIDs = \Drupal::entityQuery('node')
      ->condition('type', 'user_at_record')
      ->condition('field_user_at_record_library', true, '=')
      ->condition('uid', $user->id(), '=')
      ->execute();

    if(count($atRecordsIDs) == 0) {
      $recommendationURL = Url::fromRoute('buddy.user_search')->toString();
      $searchURL = Url::fromRoute('buddy.user_at_recommendation')->toString();
      return [
        '#type' => 'markup',
        '#markup' => '<p>'.$this->t("Your library is currently empty.").' </p><p>'
          .$this->t("Buddy can ").'<a href="'.$searchURL.'">'.$this->t("find a tool for you").'</a> '.$this->t("or you can "). '<a href="'.$searchURL.'">'.$this->t("search").'</a> '.$this->t("for a tool you like.").' </p>',
        '#title' => $title,
      ];
    }


    $html = '<div class="buddy_rec_info"> <p class="">'.$this->t("This is the list of tools you saved to your favourites.").'</p>';
    $html.= '<p>'.$this->t("Rating your favourite tools helps Buddy to improve its recommendations").'</p></div>';

    $atRecords = \Drupal::entityTypeManager()->getStorage('node')
      ->loadMultiple($atRecordsIDs);

    foreach ($atRecords as $key => $atRecord){

      $atEntryID = $atRecord->get("field_user_at_record_at_entry")->getValue()[0]['target_id'];
      $atEntry = Node::load($atEntryID);
      if(!$atEntry){
        continue;
      }
      $descriptions = Util::getDescriptionsOfATEntry($atEntryID);
      $user = \Drupal::currentUser();
      $description = Util::getDescriptionForUser($descriptions,$user);
      $languages = Util::getLanguagesOfDescriptions($descriptions);
      $platforms = Util::getPlatformsOfATEntry($atEntry);
      $supportCategories = Util::getSupportCategoriesOfAtEntry(Node::load($atEntryID));

      $content = Util::renderDescriptionTiles($description,$user,$languages,$platforms,$supportCategories,false,false);
      $content = Util::renderDescriptionTiles2($description,$supportCategories,$platforms,$languages,false,3);


      $content.='<div class="col-12 col-lg-3 buddy_favourite_col buddy_recommendation_menu">';
      $removeFromFavouritesURL  = Url::fromRoute('buddy.user_at_library_remove',['record' =>$atRecord->id()])->toString();
      $content.='<a href="'.$removeFromFavouritesURL.'" class="buddy_menu_button buddy_invert_button buddy_favourites_button"><span>'.$this->t('Remove from favourites').'</span><i class="fas fa-minus"></i><span></a>';
      $content.='</div></div>';

      $installLink = Link::createFromRoute($this->t('How to get this tool'),'buddy.user_at_install_form',['description' => $description->id(),"return"=>"library"],  ['attributes' => ['class' => ['buddy_menu_button']]])->toString()->getGeneratedLink();


      $content.= ' <div class="row">
    <div class="col-12 col-lg-6 buddy_recommendation_menu"><div class="rate_header"><strong>'.$this->t("Rate this tool:").'</strong></div>
        '.$this->rating_widget_html($user->id(), $atEntryID).'
    </div>
    <div class="col-12 col-lg-6 buddy_recommendation_menu text-align-right">
        '.$installLink.'

    </div>
    </div>';

      $html.="<div class='at_library_container'>";
      $html.=$content;
      $html .= "</div></div>";

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
      '#allowed_tags' => ['form', 'button', 'path', 'svg', 'input', 'label', 'a', 'div','img','h3','h2','h1','p', 'b', 'g',
        'strong','hr','ul','li', 'span', 'output','i'],
    );

    return $build;

    }

    public function rating_widget_html($uid, $at_id, $n_stars=5) {

      // Begin rating form
      $html = "<div class=\"rating-form-container\">";
      $html .= "<form action=\"#\" class=\"star_rating\" id=\"star_rating_{$uid}_{$at_id}\">";

      // Delete rating
      $html .= "<input value=\"0_{$uid}_{$at_id}\" id=\"star0_{$uid}_{$at_id}\" ";
      $html .= " class=\"star_cancel visuallyhidden\" checked type=\"radio\" name=\"rating\">";
      $html .= "<label for=\"star0_{$uid}_{$at_id}\">";
      $html .= "<span class=\"visuallyhidden\">";
      $html .= $this->t('Delete Rating');
      $html .= "</span><svg viewBox=\"0 0 512 512\">";
      $html .= "<g stroke-width=\"70\" stroke-linecap=\"square\">";
      $html .= "<path d=\"M91.5,442.5 L409.366489,124.633512\"></path>";
      $html .= "<path d=\"M90.9861965,124.986197 L409.184248,443.184248\"></path>";
      $html .= "</g></svg></label>";

      $ratings = array(
        1 => $this->t('Terrible!'),
        2 => $this->t('Needs improvement'),
        3 => $this->t('Okay'),
        4 => $this->t('Very good'),
        5 => $this->t('Excellent!')
      );

      $user_rating = false;
      try {
        $database = \Drupal::database();
        $user_rating = $database->query("SELECT rating FROM {rating} WHERE uid = :uid AND at_nid = :at_nid", [
          ':uid' => $uid,
          ':at_nid' => $at_id,
        ])->fetchField();
      } catch (\Exception $e) {
        watchdog_exception('buddy', $e, "Error fetching user-item rating {$uid}/{$at_id}.");
      }

      // Star ratings
      for ($i=1; $i<$n_stars+1; $i++) {
        $html .= "<input value=\"{$i}_{$uid}_{$at_id}\" id=\"star{$i}_{$uid}_{$at_id}\" ";
        if ($user_rating > 0 && $i==$user_rating) {
          $html .= "checked ";
        }
        $html .= "type=\"radio\" name=\"rating\" class=\"visuallyhidden\">";
        $html .= "<label for=\"star{$i}_{$uid}_{$at_id}\">";
        $html .= "<span class=\"visuallyhidden\">";
        if ($i <= 5) {
          $html .= $ratings[$i];
        } else {
          $html .= $this->t('@n Stars', array('@n' => $i));
        }
        $html .= "</span><svg viewBox=\"0 0 512 512\">";
        $html .= "<path d=\"M512 198.525l-176.89-25.704-79.11-160.291-79.108 160.291-176.892 25.704 128 ";
        $html .= "124.769-30.216 176.176 158.216-83.179 158.216 83.179-30.217-176.176 128.001-124.769z\">";
        $html .= "</path></svg></label>";
      }

      // Submit button
      $html .= "<button type=\"submit\" class=\"btn-small visuallyhidden focusable\">";
      $html .= $this->t('Submit my rating');
      $html .= "</button>";

      // End form
      $html .= "</form></div>";

      // Output area
      $html .= "<output id=\"msg_{$uid}_{$at_id}\" class=\"rating-output\"></output>";

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
