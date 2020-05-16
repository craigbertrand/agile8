<?php

namespace Drupal\Tests\tr_rulez\Unit\Integration\RulesAction;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Tests\rules\Unit\Integration\RulesIntegrationTestBase;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \Drupal\tr_rulez\Plugin\RulesAction\CacheTagInvalidator
 * @group RulesAction
 *
 * @requires module rules
 */
class CacheTagInvalidatorTest extends RulesIntegrationTestBase {
  /**
   * The action to be tested.
   *
   * @var \Drupal\rules\Core\RulesActionInterface
   */
  protected $action;

  /**
   * The Rules logger channel.
   *
   * @var \Psr\Log\LoggerInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $logger;

  /**
   * The cache_tags.invalidator service.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Must enable our module to make our plugins discoverable.
    $this->enableModule('tr_rulez');

    // Tell the plugin manager where to look for plugins.
    $this->moduleHandler->getModuleDirectories()
      ->willReturn(['tr_rulez' => __DIR__ . '/../../../../../']);

    $this->logger = $this->prophesize(LoggerInterface::class);
    $logger_factory = $this->prophesize(LoggerChannelFactoryInterface::class);
    $logger_factory->get('rules')->willReturn($this->logger->reveal());

    $this->cacheTagsInvalidator = $this->prophesize(CacheTagsInvalidatorInterface::class);
    $this->container->set('cache_tags.invalidator', $this->cacheTagsInvalidator->reveal());

    $this->container->set('logger.factory', $logger_factory->reveal());
    $this->action = $this->actionManager->createInstance('cache_tag_invalidator');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Cache tag invalidator', $this->action->summary());
  }

  /**
   * Tests the action execution.
   *
   * @covers ::execute
   */
  public function testActionExecution() {
    $cache_tags = ['test:tag'];
    $this->action->setContextValue('cache_tags', $cache_tags);

    $this->action->execute();

    $this->cacheTagsInvalidator->invalidateTags($cache_tags)->shouldBeCalledTimes(1);

  }

}
