<?php
namespace Drupal\buddy\Controller;

use Drupal\buddy\Util\Util;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

class ATDescriptionViewController extends ControllerBase
{


  public function ATDescriptionView(NodeInterface $description=NULL){



    $user = \Drupal::currentUser();
    $title = $description->getTitle();

    $language = $description->field_at_description_language->getValue();


    $atDescription = $description->field_at_description->getValue();


    $image = $description->field_at_description_at_image->getValue();
    $styled_image_url = ImageStyle::load('medium')->buildUrl($description->field_at_description_at_image->entity->getFileUri());



    $downloadLink = $description->field_at_description_download->getValue();

    $downloadLinkHTML = "";
    if(count($downloadLink)){
      $downloadLinkHTML = '<strong>Download: </strong><a href="'.$downloadLink[0]["uri"].'" class="button button--primary">Download</a><br>';
    }

    $html = '<div><strong>'.$this->t("Language").': </strong>'.$language[0]['value'].'</div>
<div><strong>Description:</strong><br>'.$atDescription[0]['value'].'</div>
<div><img src="'.$styled_image_url.'" alt="Preview image"></div>
<div>'.$downloadLinkHTML.'</div>';


    $backLink = Link::createFromRoute($this->t('Back'),'buddy.at_entry_overview',[],['attributes' => ['class' => 'btn btn-primary overview-button']])->toString()->getGeneratedLink();

    $html.='<br>'.$backLink;



    $revision_ids = \Drupal::entityTypeManager()->getStorage('node')->revisionIds($description);
    $last_revision_id = end($revision_ids);

    $revisionLink = "";
    if ($description->getRevisionId() != $last_revision_id) {
      $revisionLink = Link::createFromRoute($this->t('Edit revision'),'buddy.description_edit_form',['description' => $description->id()],  ['attributes' => ['class' => 'btn btn-primary overview-button']])->toString()->getGeneratedLink();
    }else{
      $revisionLink = Link::createFromRoute($this->t('Create new revision'),'buddy.description_edit_form',['description' => $description->id()],  ['attributes' => ['class' => 'btn btn-primary overview-button']])->toString()->getGeneratedLink();
    }

    $html.=$revisionLink;

    $build = array(
      '#type' => 'markup',
      '#markup' => $html,
      '#title' => $title,
    );

    return $build;

  }

}
