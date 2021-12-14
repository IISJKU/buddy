<?php


namespace Drupal\buddy\Controller;


use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Database\Query\Merge;
use Symfony\Component\HttpFoundation\JsonResponse;

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
    try {
      $connection = \Drupal::database();
      // Add to main table
      $result = $connection->merge('rating')
        ->keys(['uid' => $user->id(), 'at_nid' => $atEntry->id()])
        ->fields(['rating' => $rating, 'date' => date('Y-m-d H:i:s')])
        ->execute();
      // Add to temporary cache
      $result_cache = $connection->merge('rating_cache')
        ->keys(['uid' => $user->id(), 'at_nid' => $atEntry->id()])
        ->fields(['rating' => $rating])
        ->execute();
      $status = 'success';
      if ($result == Merge::STATUS_INSERT) {
        $msg = "Rating for user {$user->id()} and item {$atEntry->id()} added.";
        $code = 201;
      } else if ($result == Merge::STATUS_UPDATE) {
        $msg = "Rating for user {$user->id()} and item {$atEntry->id()} updated.";
        $code = 204;
      } else {
        $msg = "Unknown response.";
        $code = 202;
      }
    } catch (\Exception $e) {
      $status = 'fail';
      $msg = "An error happened and the rating could not be stored.";
      $code = 500;
      watchdog_exception('buddy', $e);
    } finally {
      return new JsonResponse(
        ['status' => $status,
          'message' => $msg],
        $code);
    }
  }
}
