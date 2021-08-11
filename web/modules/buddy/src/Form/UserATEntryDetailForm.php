<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Util\Browser;
use Drupal\buddy\Util\Util;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node;

class UserATEntryDetailForm extends FormBase
{

  protected $browser;
  protected $platform;
  protected $isMobile;


  protected $tabHeaderHTML = "";
  protected $tabPanelHTML = "";



  public function getFormId()
  {
    return "user_entry_detail_form";
  }

  public function buildForm(array $form, FormStateInterface $form_state, $description = null)
  {

    $browser = new Browser();
    $this->browser = $browser->getBrowser();
    $this->platform = $browser->getPlatform();
    $this->isMobile = $browser->isMobile();

    $markup = Util::renderDescriptionTabs($description,true);

    $form['description'] = [
      '#type' => 'markup',
      '#markup' => $markup,
      '#allowed_tags' => ['button', 'a', 'div','img','h2','h1','p','b','b','strong','hr'],

    ];




    $form['introduction_install'] = [
      '#type' => 'markup',
      '#markup' => '<hr><h2>'.$this->t("How do you get this AT?").'</h2><div><strong>'.$this->t("This at is available as:").'</strong></div>',
      '#allowed_tags' => ['button', 'a', 'div','img','h2','h1','p','b','strong','hr'],

    ];





    //Get AT Entry of description
    $atEntriesID = \Drupal::entityQuery('node')
      ->condition('type', 'at_entry')
      ->condition('field_at_descriptions', $description->id(), '=')
      ->execute();

    $atEntries = \Drupal::entityTypeManager()->getStorage('node')
      ->loadMultiple($atEntriesID);

    $atEntry = array_shift($atEntries);


    //Get all types (extensions, software and apps)
    $typesIDs = $atEntry->get("field_at_types")->getValue();
    $browserExtensions = [];
    $software = [];
    $apps = [];
    foreach ($typesIDs as $typeID){

      $type = Node::load($typeID['target_id']);

      $platform = $type->bundle();

      switch ($type->bundle()){
        case "at_type_browser_extension":{
          $browserExtensions[] = $type;
          break;
        }

        case "at_type_app": {
          $apps[] = $type;
          break;
        }

        case "at_type_software": {
          $software[] = $type;
          break;
        }
        default: {

        }
      }
    }


    Util::setTitle($description->getTitle());

    $formElements = [];
    $activeTab = true;
    if(count($browserExtensions)){

      $formElements['browser_extension'] = $this->renderBrowserExtensions($browserExtensions, $form, $activeTab);


      $activeTab = false;
    }


    if(count($software)){
      $this->renderSoftware($software,$activeTab);
      $activeTab = false;
    }

    if(count($apps)){
      $this->renderApps($apps,$activeTab);
      $activeTab = false;
    }

    $markup = '<nav>
  <div class="nav nav-tabs" id="nav-tab" role="tablist">
  '.$this->tabHeaderHTML.'  </div>
</nav>
<div class="tab-content" id="nav-tabContent">
'.$this->tabPanelHTML.'
</div>';


    $form['text'] = [
      '#type' => 'markup',
      '#markup' => $markup,
      '#allowed_tags' => ['button', 'a', 'div','img','h2','h1','p','b','b','strong','hr'],

    ];



    $form['actions']['delete'] = [
      '#type' => 'button',
      '#button_type' => 'primary',
      '#value' => $this->t('Delete'),
      '#submit' => ['::deleteFormSubmit'],
      '#prefix' => '<h1>asdfdfs</h1><p>asdfjsdklfds</p><img src="http://localhost/buddy/web//modules/buddy/img/icons/browser-icon.png" width="50" height="50" alt="" title="">'

    ];

    $form['#attached']['library'][] = 'buddy/user_at_detail';
    return $form;
  }


  protected function renderBrowserExtensions($extensions,$activeTab){

    $form = [];

    $compatibleExtensions = [];
    $otherExtensions = [];
    foreach ($extensions as $extension){

      $browser = Node::load($extension->get("field_type_browser")->getValue()[0]['target_id']);

      if(strtolower($this->browser) === strtolower($browser->getTitle())){
        $compatibleExtensions[] = ['extension' => $extension, 'browser' => $browser];
      }else{
        $otherExtensions[] = ['extension' => $extension, 'browser' => $browser];

      }


    }

    $this->tabHeaderHTML.= $this->renderTabHeader($this->t("Browser Extension"),Util::getBaseURL()."/modules/buddy/img/icons/browser-icon.png", "extension_tab","extension_tab_panel",$activeTab);


    $tabPanelHeader = $this->renderTabPanelHeader("extension_tab","extension_tab_panel",$activeTab);
    $form['intro'] = [
      '#type' => 'markup',
      '#markup' =>  $tabPanelHeader."<h3>".$this->t("This browser extension is available for:")."</h3>",
      '#allowed_tags' => ['button', 'a', 'div','img','h2','h1','p','b','b','strong','hr'],

    ];

    $extensionHTML = "<h3>".$this->t("This browser extension is available for:")."</h3>";

    foreach ($compatibleExtensions as $currentExtension){

      $icon = $currentExtension['browser']->field_icon->getValue();
      $altText = $icon[0]['alt'];
      $styled_image_url = ImageStyle::load('medium')->buildUrl($currentExtension['browser']->field_icon->entity->getFileUri());


      $extensionHTML.= "<h3>".$currentExtension['browser']->getTitle()."</h3><div><div><b>".$this->t('You are currently using this browser.')."</b></div><p>
                    <img src='".$styled_image_url."' alt='".$altText."' class='buddy-type-icon'>".$currentExtension['browser']->field_description->getValue()[0]['value']."</p></div>";
      $extensionHTML.= $this->createDownloadLink("asdf","Download the extension");


      $extensionDescription = "
            <h3>".$currentExtension['browser']->getTitle()."</h3>
            <div>
                <div>
                    <b>".$this->t('You are currently using this browser.')."</b>
                </div>
                <p>
                   <img src='".$styled_image_url."' alt='".$altText."' class='buddy-type-icon'>
                   ".$currentExtension['browser']->field_description->getValue()[0]['value']."

                </p>
                </div>
            </div>";


      $form['extension_'.$currentExtension['extension']->id()] = [
        '#type' => 'button',
        '#button_type' => 'primary',
        '#value' => $this->t('Delete'),
        '#submit' => ['::deleteFormSubmit'],
        '#prefix' => $extensionDescription,

      ];

    }

    foreach ($otherExtensions as $currentExtension){

      $icon = $currentExtension['browser']->field_icon->getValue();
      $altText = $icon[0]['alt'];
      $styled_image_url = ImageStyle::load('medium')->buildUrl($currentExtension['browser']->field_icon->entity->getFileUri());

      $extensionHTML.="<hr>";
      $extensionHTML.= "<h3>".$currentExtension['browser']->getTitle()."</h3><p><img src='".$styled_image_url."' alt='".$altText."' class='buddy-type-icon'>".$currentExtension['browser']->field_description->getValue()[0]['value']."</p>";


      $extensionDescription = "
            <hr>
            <h3>".$currentExtension['browser']->getTitle()."</h3>
            <div>
                <p>
                   <img src='".$styled_image_url."' alt='".$altText."' class='buddy-type-icon'>
                   ".$currentExtension['browser']->field_description->getValue()[0]['value']."

                </p>
                </div>
            </div>";


      $form['extension_'.$currentExtension['extension']->id()] = [
        '#type' => 'button',
        '#button_type' => 'primary',
        '#value' => $this->t('Delete'),
        '#submit' => ['::deleteFormSubmit'],
        '#prefix' => $extensionDescription,
        ];


      $extensionHTML.= $this->createDownloadLink("asdf","Download the extension");
    }

    $form['outro'] = [
      '#type' => 'markup',
      '#markup' =>  '</div>',
      '#allowed_tags' => ['div'],

    ];


    $this->tabPanelHTML.= $this->renderTabPanel("extension_tab","extension_tab_panel",$activeTab,$extensionHTML);

    return $form;

  }

  protected function renderApps($apps,$activeTab){

    $compatibleApps = [];
    $otherApps = [];
    foreach ($apps as $application){

    //  $os = $application->get("field_app_operating_system")->getValue()[0]['value'];
      $appOs = Node::load($application->get("field_app_os")->getValue()[0]['target_id']);

      if(strtolower($this->platform) === strtolower($appOs->getTitle())){
        $compatibleApps[] = ['app' => $application, 'os' => $appOs];
      }else{

        $otherApps[] = ['app' => $application, 'os' => $appOs];
      }


    }
    $this->tabHeaderHTML.= $this->renderTabHeader($this->t("Apps"),Util::getBaseURL()."/modules/buddy/img/icons/app-icon.png", "app_tab","app_tab_panel",$activeTab);


    $appHTML = '<h2>'.$this->t("This assistive technology is available for the following operating system(s):").'</h2>';
    foreach ($compatibleApps as $currentApp){

      $icon = $currentApp['os']->field_icon->getValue();
      $altText = $icon[0]['alt'];
      $styled_image_url = ImageStyle::load('medium')->buildUrl($currentApp['os']->field_icon->entity->getFileUri());


      $appHTML.= "<h3>".$currentApp['os']->getTitle()."</h3><p><b>".$this->t('You are currently using this system.')."</b>
                    <br><img src='".$styled_image_url."' alt='".$altText."' class='buddy-type-icon'>".$currentApp['os']->field_description->getValue()[0]['value']."</p>";
      $appHTML.= $this->createDownloadLink("asdf","Download the extension");
    }

    foreach ($otherApps as $currentApp){

      $icon = $currentApp['os']->field_icon->getValue();
      $altText = $icon[0]['alt'];
      $styled_image_url = ImageStyle::load('medium')->buildUrl($currentApp['os']->field_icon->entity->getFileUri());

      $appHTML.="<hr>";
      $appHTML.= "<h3>".$currentApp['os']->getTitle()."</h3><p><img src='".$styled_image_url."' alt='".$altText."' class='buddy-type-icon'>".$currentApp['os']->field_description->getValue()[0]['value']."</p>";
      $appHTML.= $this->createDownloadLink("asdf","Download the extension");
    }


    $this->tabPanelHTML.= $this->renderTabPanel("app_tab","app_tab_panel",$activeTab,$appHTML);
  }

  protected function renderSoftware($software,$activeTab){
    $compatibleSoftware = [];
    $otherSoftware = [];
    foreach ($software as $currentSoft){

      $os = $currentSoft->get("field_type_desk_operating_system")->getValue()[0]['value'];
      $desktopOS = Node::load($currentSoft->get("field_type_software_os")->getValue()[0]['target_id']);

      $generalOS = "N/A";
      if(str_contains($os,"win")){
        $generalOS = "windows";
      }else if(str_contains($os,"linux")){
        $generalOS = "linux";
      }else if(str_contains($os,"osx")){
        $generalOS = "osx";
      }

      if(strtolower($this->platform) === strtolower($desktopOS->getTitle())){
        $compatibleSoftware[] = ['software' => $currentSoft, 'os' => $desktopOS];
      }else{

        $otherSoftware[] =  ['software' => $currentSoft, 'os' => $desktopOS];;

      }


    }

    $this->tabHeaderHTML.= $this->renderTabHeader($this->t("Software"),Util::getBaseURL()."/modules/buddy/img/icons/desktop-icon.png", "software_tab","software_tab_panel",$activeTab);

    $softwareHTML =  '<h2>'.$this->t("This assistive technology is available for the following  desktop operating system(s):").'</h2>';
    foreach ($compatibleSoftware as $currentSoft){

      $icon = $currentSoft['os']->field_icon->getValue();
      $altText = $icon[0]['alt'];
      $styled_image_url = ImageStyle::load('medium')->buildUrl($currentSoft['os']->field_icon->entity->getFileUri());


      $softwareHTML.= "<h3>".$currentSoft['os']->getTitle()."</h3><p><b>".$this->t('You are currently using this system.')."</b>
                    <br><img src='".$styled_image_url."' alt='".$altText."' class='buddy-type-icon'>".$currentSoft['os']->field_description->getValue()[0]['value']."</p>";
      $softwareHTML.= $this->createDownloadLink("asdf","Download the extension");
    }

    foreach ($otherSoftware as $currentSoft){
      $icon = $currentSoft['os']->field_icon->getValue();
      $altText = $icon[0]['alt'];
      $styled_image_url = ImageStyle::load('medium')->buildUrl($currentSoft['os']->field_icon->entity->getFileUri());

      $softwareHTML.="<hr>";
      $softwareHTML.= "<h3>".$currentSoft['os']->getTitle()."</h3><p><img src='".$styled_image_url."' alt='".$altText."' class='buddy-type-icon'>".$currentSoft['os']->field_description->getValue()[0]['value']."</p>";
      $softwareHTML.= $this->createDownloadLink("asdf","Download the extension");
    }


    $this->tabPanelHTML.= $this->renderTabPanel("software_tab","software_tab_panel",$activeTab,$softwareHTML);


  }


  protected function renderTabHeader($name, $icon, $tabID, $tabPanelID, $activeTab){

    $active = "";
    $ariaSelected = "false";
    if($activeTab){
      $active = "active";
      $ariaSelected = "true";
    }

    return '<a class="nav-link '.$active.'" id="'.$tabID.'" data-toggle="tab" href="#'.$tabPanelID.'" role="tab" aria-controls="'.$tabPanelID.'" aria-selected="'.$ariaSelected.'"><img src="'.$icon.'" width="50" height="50" alt="" title="">'.$name.'</a>';

  }

  protected function renderTabPanel($tabID, $tabPanelID, $activeTab, $html){

    $activeTabHTML = "";
    if($activeTab){
      $activeTabHTML =" show active";
    }
    return '  <div class="tab-pane fade'.$activeTabHTML.'" id="'.$tabPanelID.'" role="tabpanel" aria-labelledby="'.$tabID.'">'.$html.'</div>';
  }

  protected function renderTabPanelHeader($tabID, $tabPanelID, $activeTab){

    $activeTabHTML = "";
    if($activeTab){
      $activeTabHTML =" show active";
    }
    return '  <div class="tab-pane fade'.$activeTabHTML.'" id="'.$tabPanelID.'" role="tabpanel" aria-labelledby="'.$tabID.'">';
  }


  protected function createDownloadLink($url,$linkText = "Download"){

    return '<a href="'.$url.'"><img src="'.Util::getBaseURL()."/modules/buddy/img/icons/download-icon.png".'" alt="" class="buddy-download-icon">'.$linkText.'</a>';
  }




  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    // TODO: Implement submitForm() method.
  }

}
