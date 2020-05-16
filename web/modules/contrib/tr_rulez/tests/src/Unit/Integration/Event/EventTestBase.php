<?php

namespace Drupal\Tests\tr_rulez\Unit\Integration\Event;

use Drupal\rules\Core\RulesEventManager;
use Drupal\Tests\rules\Unit\Integration\Event\EventTestBase as RulesEventTestBase;

/**
 * Base class containing common code for tr_rulez event tests.
 *
 * @group tr_rulez
 *
 * @requires module rules
 */
abstract class EventTestBase extends RulesEventTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Must enable our module to make our plugins discoverable.
    $this->enableModule('tr_rulez', [
      'Drupal\\tr_rulez' => __DIR__ . '/../../../../../src',
    ]);

    // Tell the plugin manager where to look for plugins.
    $this->moduleHandler->getModuleDirectories()
      ->willReturn(['tr_rulez' => __DIR__ . '/../../../../../']);

    // Create a real plugin manager with a mock moduleHandler.
    $this->eventManager = new RulesEventManager($this->moduleHandler->reveal(), $this->entityTypeBundleInfo->reveal());
  }

}
