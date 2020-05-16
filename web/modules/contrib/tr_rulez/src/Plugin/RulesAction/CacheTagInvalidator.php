<?php

namespace Drupal\tr_rulez\Plugin\RulesAction;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Core\RulesActionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides "Cache Tag Invalidator" rules action.
 *
 * @RulesAction(
 *   id = "cache_tag_invalidator",
 *   label = @Translation("Cache tag invalidator"),
 *   category = @Translation("System"),
 *   context_definitions = {
 *     "cache_tags" = @ContextDefinition("string",
 *       label = @Translation("Cache tag(s) to invalidate"),
 *       multiple = TRUE
 *     ),
 *   }
 * )
 */
class CacheTagInvalidator extends RulesActionBase implements ContainerFactoryPluginInterface {

  /**
   * The logger for the rules channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The cache_tags.invalidator service.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * Constructs a CacheTagInvalidator object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cacheTagsInvalidator
   *   The cache_tags.invalidator service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelFactoryInterface $logger_factory, CacheTagsInvalidatorInterface $cacheTagsInvalidator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger_factory->get('rules');
    $this->cacheTagsInvalidator = $cacheTagsInvalidator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory'),
      $container->get('cache_tags.invalidator')
    );
  }

  /**
   * Invalidate the cache tags.
   *
   * @param string[] $cache_tags
   *   The cache tags to invalidate.
   */
  protected function doExecute(array $cache_tags) {
    $this->logger->info('Invalidating cache tag(s): %tags', ['%tags' => implode(', ', $cache_tags)]);
    $this->cacheTagsInvalidator->invalidateTags($cache_tags);
  }

}
