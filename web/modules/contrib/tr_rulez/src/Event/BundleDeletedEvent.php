<?php

namespace Drupal\tr_rulez\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Represent various entity bundle events.
 *
 * @see rules_entity_presave()
 */
class BundleDeletedEvent extends Event {

  const EVENT_NAME = 'tr_rulez_entity_bundle_delete';

  /**
   * The entity type name. 'node', 'user', etc.
   *
   * @var string
   */
  public $entity_type;

  /**
   * The bundle name.
   *
   * @var string
   */
  public $bundle_name;

  /**
   * Constructs the object.
   *
   * @param string $entity_type
   *   The entity type name.
   * @param string $bundle_name
   *   The bundle name.
   */
  public function __construct($entity_type, $bundle_name) {
    $this->entity_type = $entity_type;
    $this->bundle_name = $bundle_name;
  }

}
