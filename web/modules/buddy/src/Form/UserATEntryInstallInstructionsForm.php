<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Util\Browser;
use Drupal\buddy\Util\Util;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node;
use http\Url;

class UserATEntryInstallInstructionsForm extends FormBase
{

  protected $browser;
  protected $platform;
  protected $isMobile;



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


    Util::setTitle($this->t("Install instructions for:")." ".$description->getTitle());

    $formElements = [];
    $activeTab = true;
    if(count($browserExtensions)){

      $formElements['browser_extension'] = $this->renderBrowserExtensions($browserExtensions, $form, $activeTab);


      $activeTab = false;
    }


    if(count($software)){
      $formElements['software'] = $this->renderSoftware($software,$activeTab);
      $activeTab = false;
    }

    if(count($apps)){
      $formElements['apps'] = $this->renderApps($apps,$activeTab);
      $activeTab = false;
    }

    $tabHeader = "";
    foreach ($formElements as $key => $value){
      $tabHeader.= $value['tab_header'];
    }

    $markup = '<nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        '.$tabHeader.'
                    </div>
                </nav>
                <div class="tab-content" id="nav-tabContent">';


    $form['tab_list_start'] = [
      '#type' => 'markup',
      '#markup' => $markup,
      '#allowed_tags' => ['button', 'a', 'div','img','h2','h1','p','b','b','strong','hr'],

    ];

    foreach ($formElements as $key => $value){


      $form[$key] = $value['form'];
    }


    $form['tab_list_end'] = [
      '#type' => 'markup',
      '#markup' => '</div>',
      '#allowed_tags' => ['div'],

    ];

    $markup = Link::createFromRoute($this->t('Back to my AT library'),'buddy.user_at_library',[],  ['attributes' => ['class' => 'btn btn-primary overview-button']])->toString()->getGeneratedLink();

    $form['links'] = [
      '#type' => 'markup',
      '#markup' => $markup,
      '#prefix' => '<hr>',
      '#allowed_tags' => ['button', 'a', 'div','img','h2','h1','p','b','b','strong','hr'],

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

    $tabHeader = $this->renderTabHeader($this->t("Browser Extension"),Util::getBaseURL(false)."/modules/buddy/img/icons/browser-icon.png", "extension_tab","extension_tab_panel",$activeTab);

    $tabPanelHeader = $this->renderTabPanelHeader("extension_tab","extension_tab_panel",$activeTab);
    $form['intro'] = [
      '#type' => 'markup',
      '#markup' =>  $tabPanelHeader."<h3>".$this->t("This browser extension is available for:")."</h3>",
      '#allowed_tags' => ['button', 'a', 'div','img','h2','h1','p','b','b','strong','hr'],

    ];


    foreach ($compatibleExtensions as $currentExtension){

      $form['extension_'.$currentExtension['extension']->id()] = $this->renderBrowserDescription($currentExtension['browser'],$currentExtension['extension']->id(), $this->t('You are currently using this browser.'));

    }

    foreach ($otherExtensions as $currentExtension){

      $form['extension_'.$currentExtension['extension']->id()] = $this->renderBrowserDescription($currentExtension['browser'],$currentExtension['extension']->id());

    }

    $form['outro'] = [
      '#type' => 'markup',
      '#markup' =>  '</div>',
      '#allowed_tags' => ['div'],

    ];

    return ["form" => $form, "tab_header" => $tabHeader];

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

    $tabHeader = $this->renderTabHeader($this->t("Software"),Util::getBaseURL(false)."/modules/buddy/img/icons/desktop-icon.png", "software_tab","software_tab_panel",$activeTab);
    $tabPanelHeader = $this->renderTabPanelHeader("software_tab","software_tab_panel",$activeTab);

    $form['intro'] = [
      '#type' => 'markup',
      '#markup' =>  $tabPanelHeader."<h3>".$this->t("This software is available for the following  desktop operating system(s):")."</h3>",
      '#allowed_tags' => ['button', 'a', 'div','img','h2','h1','p','b','b','strong','hr'],

    ];


    foreach ($compatibleSoftware as $currentSoft){

      $form['software_'.$currentSoft['software']->id()] = $this->renderOsDescription($currentSoft['os'],$currentSoft['software']->id(), $this->t('You are currently using this operating system.'));


    }

    foreach ($otherSoftware as $currentSoft){
      $form['software_'.$currentSoft['software']->id()] = $this->renderOsDescription($currentSoft['os'],$currentSoft['software']->id());

    }

    $form['outro'] = [
      '#type' => 'markup',
      '#markup' =>  '</div>',
      '#allowed_tags' => ['div'],

    ];

    return ["form" => $form, "tab_header" => $tabHeader];

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

    $tabHeader = $this->renderTabHeader($this->t("Apps"),Util::getBaseURL(false)."/modules/buddy/img/icons/app-icon.png", "app_tab","app_tab_panel",$activeTab);

    $tabPanelHeader = $this->renderTabPanelHeader("app_tab","app_tab_panel",$activeTab);
    $form['intro'] = [
      '#type' => 'markup',
      '#markup' =>  $tabPanelHeader."<h3>".$this->t("This apps are available for the following system(s):")."</h3>",
      '#allowed_tags' => ['button', 'a', 'div','img','h2','h1','p','b','b','strong','hr'],

    ];



    $appHTML = '<h2>'.$this->t("This assistive technology is available for the following operating system(s):").'</h2>';
    foreach ($compatibleApps as $currentApp){




      $form['app_'.$currentApp['app']->id()] = $this->renderOsDescription($currentApp['os'],$currentApp['app']->id(), $this->t('You are currently using this operating system.'));


    }

    foreach ($otherApps as $currentApp){
      $form['app_'.$currentApp['app']->id()] = $this->renderOsDescription($currentApp['os'],$currentApp['app']->id(), $this->t('You are currently using this operating system.'));

    }

    $form['outro'] = [
      '#type' => 'markup',
      '#markup' =>  '</div>',
      '#allowed_tags' => ['div'],

    ];

    return ["form" => $form, "tab_header" => $tabHeader];
  }


  protected function renderTypeDescription($type,$id,$currentMessage){
    $icon = $type->field_icon->getValue();
    $altText = $icon[0]['alt'];
    $styled_image_url = ImageStyle::load('medium')->buildUrl($type->field_icon->entity->getFileUri());
    $description = "";
    if($currentMessage != ""){
      $description.="<hr>";
      $currentMessage = "<div><strong>".$currentMessage."</strong></div>";
    }

    $description.= "
            <h3>".$type->getTitle()."</h3>
            ".$currentMessage."
            <div>
                <p>
                   <img src='".$styled_image_url."' alt='".$altText."' class='buddy-type-icon'>
                   ".$type->field_description->getValue()[0]['value']."

                </p>
            </div>";


    return [
      '#type' => 'submit',
      '#name' => $id,
      '#button_type' => 'primary',
      '#value' => $this->t('Get it!'),
      '#prefix' => $description,

    ];


  }

  protected function renderBrowserDescription($browser,$id,$currentBrowserMessage =""){
    return $this->renderTypeDescription($browser,$id,$currentBrowserMessage);
  }

  protected function renderOsDescription($os,$id, $currentOSMessage = ""){
    return $this->renderTypeDescription($os,$id, $currentOSMessage);
  }

  protected function renderMobileOSDescription($os,$id, $currentOSMessage = ""){

    return $this->renderTypeDescription($os,$id, $currentOSMessage);
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


  protected function renderTabPanelHeader($tabID, $tabPanelID, $activeTab){

    $activeTabHTML = "";
    if($activeTab){
      $activeTabHTML =" show active";
    }
    return '  <div class="tab-pane fade'.$activeTabHTML.'" id="'.$tabPanelID.'" role="tabpanel" aria-labelledby="'.$tabID.'">';
  }






  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    $typID  = $form_state->getTriggeringElement()['#name'];

    $type = Node::load($typID);

    $link = $type->get("field_type_download_link")->getValue();


    $form_state->setResponse(new TrustedRedirectResponse($link[0]['uri']));

  }

}
