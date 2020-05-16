<?php

namespace Drupal\Tests\tr_rulez\Unit\Integration\Event;

/**
 * Checks that the event "tr_rulez_entity_bundle_create" is correctly defined.
 *
 * @coversDefaultClass \Drupal\tr_rulez\Event\BundleCreatedEvent
 * @group tr_rulez
 *
 * @requires module rules
 */
class BundleCreatedTest extends EventTestBase {

  /**
   * Tests the event metadata.
   */
  public function testBundleCreatedEvent() {
    $plugin_definition = $this->eventManager->getDefinition('tr_rulez_entity_bundle_create');
    $this->assertSame('After creating a new entity bundle', (string) $plugin_definition['label']);

    $event = $this->eventManager->createInstance('tr_rulez_entity_bundle_create');
    $type_context_definition = $event->getContextDefinition('entity_type');
    $this->assertSame('string', $type_context_definition->getDataType());
    $this->assertSame('Entity type', $type_context_definition->getLabel());
    $name_context_definition = $event->getContextDefinition('bundle_name');
    $this->assertSame('string', $name_context_definition->getDataType());
    $this->assertSame('Bundle name', $name_context_definition->getLabel());
  }

}
