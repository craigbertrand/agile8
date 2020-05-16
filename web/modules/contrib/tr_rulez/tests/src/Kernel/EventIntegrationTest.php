<?php

namespace Drupal\Tests\tr_rulez\Kernel;

use Drupal\rules\Context\ContextConfig;
use Drupal\user\Entity\User;
use Drupal\Tests\rules\Kernel\RulesKernelTestBase;

/**
 * Test for the Symfony event mapping to Rules events.
 *
 * @group tr_rulez
 */
class EventIntegrationTest extends RulesKernelTestBase {

  /**
   * The entity storage for Rules config entities.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'tr_rulez',
    'rules',
    'typed_data',
    'field',
    'node',
    'text',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->storage = $this->container->get('entity_type.manager')->getStorage('rules_reaction_rule');

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');

    $this->installConfig(['system']);
    $this->installConfig(['field']);
    $this->installConfig(['node']);
    $this->installSchema('node', ['node_access']);
    $this->installSchema('system', ['sequences']);
  }

  /**
   * Tests that blocking a user user triggers the Rules event listener.
   */
  public function testUserWasBlockedEvent() {
    $rule = $this->expressionManager->createRule();
    $rule->addCondition('rules_test_true');
    $rule->addAction('rules_test_debug_log',
      ContextConfig::create()
        ->map('message', 'account.name.0.value')
    );

    $config_entity = $this->storage->create([
      'id' => 'test_rule',
      'events' => [['event_name' => 'tr_rulez_user_was_blocked']],
      'expression' => $rule->getConfiguration(),
    ]);
    $config_entity->save();

    // The logger instance has changed, refresh it.
    $this->logger = $this->container->get('logger.channel.rules_debug');
    $this->logger->addLogger($this->debugLog);

    // Create an active user.
    $account = User::create(['name' => 'test_user', 'status' => TRUE]);
    $account->save();

    $account = User::load($account->id());
    // Block the user, which should trigger the rule.
    $account->block();
    $account->save();

    // Test that the action in the rule logged something.
    $this->assertRulesDebugLogEntryExists('test_user');
  }

  /**
   * Tests that unblocking a user user triggers the Rules event listener.
   */
  public function testUserWasUnblockedEvent() {
    $rule = $this->expressionManager->createRule();
    $rule->addCondition('rules_test_true');
    $rule->addAction('rules_test_debug_log');

    $config_entity = $this->storage->create([
      'id' => 'test_rule',
      'events' => [['event_name' => 'tr_rulez_user_was_unblocked']],
      'expression' => $rule->getConfiguration(),
    ]);
    $config_entity->save();

    // The logger instance has changed, refresh it.
    $this->logger = $this->container->get('logger.channel.rules_debug');
    $this->logger->addLogger($this->debugLog);

    // Create a blocked user.
    $account = User::create(['name' => 'test_user', 'status' => FALSE]);
    $account->save();

    // Activate the user, which should trigger the rule.
    $account->activate();
    $account->save();

    // Test that the action in the rule logged something.
    $this->assertRulesDebugLogEntryExists('action called');
  }

}
