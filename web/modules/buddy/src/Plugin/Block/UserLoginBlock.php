<?php


namespace Drupal\buddy\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Example: uppercase this please' block.
 *
 * @Block(
 *   id = "buddy_user_login_blick",
 *   admin_label = @Translation("Test12354")
 * )
 */
class UserLoginBlock extends BlockBase
{
  protected $entityManager;


  protected $entityFormBuilder;
  /**
   * @inheritDoc
   */
  public function build()
  {

    $this->entityFormBuilder = \Drupal::service('entity.form_builder');
    $this->entityManager =  \Drupal::entityTypeManager();

    $build = array();

    $account = $this->entityManager->getStorage('user') ->create(array());
    $build['form'] = $this->entityFormBuilder->getForm($account, 'register');

    $build['form']['account']['mail']['#description'] = t('asdfdasf');

    $build['form']['account']['back'] = [
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      '#weight' => -1,
      // Custom submission handler for 'Back' button.
      '#submit' => ['::pageTwoBackSubmit'],
      // We won't bother validating the required 'color' field, since they
      // have to come back to this page to submit anyway.
      '#limit_validation_errors' => [],
    ];

    return $build;
  }

  public function pageTwoBackSubmit(array &$form, FormStateInterface $form_state) {
    $form_state
      // Restore values for the first step.
      ->setValues($form_state->get('page_values'))
      ->set('page_num', 1)
      // Since we have logic in our buildForm() method, we have to tell the form
      // builder to rebuild the form. Otherwise, even though we set 'page_num'
      // to 1, the AJAX-rendered form will still show page 2.
      ->setRebuild(TRUE);
  }
}
