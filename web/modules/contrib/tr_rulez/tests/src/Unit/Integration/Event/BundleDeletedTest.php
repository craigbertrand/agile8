<?php

namespace Drupal\Tests\tr_rulez\Unit\Integration\Event;

/**
 * Checks that the event "tr_rulez_entity_bundle_delete" is correctly defined.
 *
 * @coversDefaultClass \Drupal\tr_rulez\Event\BundleDeletedEvent
 * @group tr_rulez
 *
 * @requires module rules
 */
class BundleDeletedTest extends EventTestBase {

  /**
   * Tests the event metadata.
   */
  public function testBundleDeletedEvent() {
    $plugin_definition = $this->eventManager->getDefinition('tr_rulez_entity_bundle_delete');
    $this->assertSame('After deleting an entity bundle', (string) $plugin_definition['label']);

    $event = $this->eventManager->createInstance('tr_rulez_entity_bundle_delete');
    $type_context_definition = $event->getContextDefinition('entity_type');
    $this->assertSame('string', $type_context_definition->getDataType());
    $this->assertSame('Entity type', $type_context_definition->getLabel());
    $name_context_definition = $event->getContextDefinition('bundle_name');
    $this->assertSame('string', $name_context_definition->getDataType());
    $this->assertSame('Bundle name', $name_context_definition->getLabel());
  }

}
