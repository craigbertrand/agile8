<?php

namespace Drupal\tr_rulez\Event;

use Drupal\user\UserInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event that is fired when a user is unblocked.
 *
 * @see tr_rulez_user_update()
 */
class UserWasUnblockedEvent extends Event {

  const EVENT_NAME = 'tr_rulez_user_was_unblocked';

  /**
   * The user account.
   *
   * @var \Drupal\user\UserInterface
   */
  public $account;

  /**
   * Constructs the object.
   *
   * @param \Drupal\user\UserInterface $account
   *   The account of the user after unblocking.
   */
  public function __construct(UserInterface $account) {
    $this->account = $account;
  }

}
