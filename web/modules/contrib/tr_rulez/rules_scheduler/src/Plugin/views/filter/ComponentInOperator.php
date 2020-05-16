<?php

namespace Drupal\rules_scheduler\Plugin\views\filter;

use Drupal\Core\Database\Connection;
use Drupal\views\Plugin\views\filter\InOperator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter handler for scheduler components.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("component_in_operator")
 */
class ComponentInOperator extends InOperator {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getValueOptions() {
    if (!isset($this->valueOptions)) {
      $this->valueTitle = $this->t('Component');
      $result = $this->database->select('rules_scheduler', 'r')
        ->fields('r', ['config'])
        ->distinct()
        ->execute();
      $config_names = [];
      foreach ($result as $record) {
        $config_names[$record->config] = $record->config;
      }
      $this->valueOptions = $config_names;
    }
    return $this->valueOptions;
  }

}
