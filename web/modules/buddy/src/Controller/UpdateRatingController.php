<?php


namespace Drupal\buddy\Controller;


use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;

class UpdateRatingController extends ControllerBase
{

  /**
   * Checks access for adding a user-item rating.
   *
   * @param AccountInterface $account
   *   Account performing the request
   * @param AccountInterface $user
   *   Account subject of the rating
   * @param NodeInterface $atEntry
   *   AT entry object of the rating
   * @param integer $rating
   *   Numeric rating
   * @return AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account, AccountInterface $user, NodeInterface $atEntry, int $rating)
  {
    if ($atEntry->bundle() == 'at_entry' && $rating >= 0) {
      if ($account->id() == $user->id()) {
        return AccessResult::allowed();
      }
      if (in_array("administrator", $account->getRoles())) {
        return AccessResult::allowed();
      }
    }
    return AccessResult::forbidden();
  }

  public function UpdateRating(AccountInterface $user, NodeInterface $atEntry, int $rating)
  {

    $db_entry = [
      'uid' => $user->id(),
      'at_nid' => $atEntry->id(),
      'rating' => $rating,
    ];
    $connection = \Drupal::database();
    $connection->insert('rating')->fields($db_entry)->execute();

    $connection->merge('example')
      ->keys(['uid' => $user->id(), 'at_nid' => $atEntry->id()])
      ->fields(['rating' => $rating])
      ->execute();
  }

}
