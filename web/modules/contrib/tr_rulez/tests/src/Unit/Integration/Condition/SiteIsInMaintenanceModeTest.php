<?php

namespace Drupal\Tests\tr_rulez\Unit\Integration\Condition;

use Drupal\Core\KeyValueStore\KeyValueMemoryFactory;
use Drupal\Core\State\State;
use Drupal\Tests\rules\Unit\Integration\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\tr_rulez\Plugin\Condition\SiteIsInMaintenanceMode
 * @group RulesCondition
 */
class SiteIsInMaintenanceModeTest extends RulesIntegrationTestBase {

  /**
   * The condition to be tested.
   *
   * @var \Drupal\rules\Core\RulesConditionInterface
   */
  protected $condition;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Must enable our module to make our plugins discoverable.
    $this->enableModule('tr_rulez');

    // Tell the plugin manager where to look for plugins.
    $this->moduleHandler->getModuleDirectories()
      ->willReturn(['tr_rulez' => __DIR__ . '/../../../../../']);

    // Create a state service to use.
    $this->state = new State(new KeyValueMemoryFactory());
    $this->container->set('state', $this->state);

    // The condition to test.
    $this->condition = $this->conditionManager->createInstance('rules_site_is_in_maintenance_mode');
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluation() {
    // The condition should return FALSE, when maintenance mode is FALSE.
    $this->state->set('system.maintenance_mode', FALSE);
    $this->assertFalse($this->condition->evaluate());

    // Now turn on maintenance mode and the condition should return TRUE.
    $this->state->set('system.maintenance_mode', TRUE);
    $this->assertTrue($this->condition->evaluate());
  }

}
