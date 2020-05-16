<?php

namespace Drupal\Tests\tr_rulez\Unit\Integration\Event;

/**
 * Checks that the event "tr_rulez_user_was_unblocked" is correctly defined.
 *
 * @coversDefaultClass \Drupal\tr_rulez\Event\UserWasUnblockedEvent
 * @group tr_rulez
 *
 * @requires module rules
 */
class UserWasUnblockedTest extends EventTestBase {

  /**
   * Tests the event metadata.
   */
  public function testUserWasUnblockedEvent() {
    $event = $this->eventManager->createInstance('tr_rulez_user_was_unblocked');
    $user_context_definition = $event->getContextDefinition('account');
    $this->assertSame('entity:user', $user_context_definition->getDataType());
    $this->assertSame('Updated user', $user_context_definition->getLabel());
  }

}
