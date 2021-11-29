<?php
namespace Drupal\buddy\Controller;

use Drupal\buddy\Util\Util;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

class ATDescriptionViewController extends ControllerBase
{

  public function ATDescriptionView(NodeInterface $description=NULL) {

    /*
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

    if ($description->getRevisionId() != $last_revision_id) {
      $revisionLink = Link::createFromRoute($this->t('Edit revision'),'buddy.description_edit_form',['description' => $description->id()],  ['attributes' => ['class' => 'btn btn-primary overview-button']])->toString()->getGeneratedLink();
    }else{
      $revisionLink = Link::createFromRoute($this->t('Create new revision'),'buddy.description_edit_form',['description' => $description->id()],  ['attributes' => ['class' => 'btn btn-primary overview-button']])->toString()->getGeneratedLink();
    }

    $html.=$revisionLink;
    */

    $atEntriesID = \Drupal::entityQuery('node')
      ->condition('type', 'at_entry')
      ->condition('field_at_descriptions', $description->id(), '=')
      ->execute();

    $atEntryID = intval(array_shift($atEntriesID));
    $descriptions = Util::getDescriptionsOfATEntry($atEntryID);
    $user = \Drupal::currentUser();
    $description = Util::getDescriptionForUser($descriptions,$user);
    $languages = Util::getLanguagesOfDescriptions($descriptions);
    $platforms = Util::getPlatformsOfATEntry(Node::load($atEntryID));
    $html= "<h2>Preview</h2>";
    $html.= Util::renderDescriptionTiles($description,$user,$languages,$platforms);
    $html.= "<hr><h2>Full view</h2>";
    $html.= Util::renderDescriptionDetail($description,$languages);

      $build = array(
      '#type' => 'markup',
      '#markup' => $html,
      '#title' => "muh",
    );

    return $build;

    $display = EntityViewDisplay::create([
      'targetEntityType' => 'node',
      'bundle' => 'at_description',
      'status' => TRUE,
    ]);
    foreach ($description->getFields() as $field) {
      $display->setComponent($field->definition['field_name'], [
        'type' => $field->options['type'],
        'settings' => $field->options['settings'],
      ]);
    }

    // Invoke all implementations of hook_node_view
    $module_handler = \Drupal::moduleHandler();
    $module_handler->invokeAll('node_view', [$build, $description, $display, 'full']);

    return $build;

  }

}
