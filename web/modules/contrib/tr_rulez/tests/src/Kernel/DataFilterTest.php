<?php

namespace Drupal\Tests\tr_rulez\Kernel;

use Drupal\Core\TypedData\DataDefinition;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests using typed data filters.
 *
 * @group tr_rulez
 *
 * @coversDefaultClass \Drupal\typed_data\DataFilterManager
 */
class DataFilterTest extends KernelTestBase {

  /**
   * The typed data manager.
   *
   * @var \Drupal\Core\TypedData\TypedDataManagerInterface
   */
  protected $typedDataManager;

  /**
   * The data filter manager.
   *
   * @var \Drupal\typed_data\DataFilterManagerInterface
   */
  protected $dataFilterManager;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['tr_rulez', 'rules', 'typed_data'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->typedDataManager = $this->container->get('typed_data_manager');
    $this->dataFilterManager = $this->container->get('plugin.manager.typed_data_filter');
  }

  /**
   * Tests the operation of the 'link' data filter.
   *
   * @covers \Drupal\tr_rulez\Plugin\TypedDataFilter\LinkFilter
   */
  public function testLinkFilter() {
    $filter = $this->dataFilterManager->createInstance('link');
    $data = $this->typedDataManager->create(DataDefinition::create('uri'), 'https://www.example.com/');

    $this->assertTrue($filter->canFilter($data->getDataDefinition()));
    $this->assertFalse($filter->canFilter(DataDefinition::create('any')));

    $this->assertEquals('string', $filter->filtersTo($data->getDataDefinition(), [])->getDataType());

    // Verify that one argument is required.
    $fails = $filter->validateArguments($data->getDataDefinition(), [/* No arguments given */]);
    $this->assertEquals(1, count($fails));
    $this->assertContains('Missing arguments for filter', (string) $fails[0]);

    // @todo Fix core bug in PrimitiveTypeConstraintValidator.php
    // Specifically, Creating a 'uri' data definition should not crash if
    // passed a non-string argument (such as an object). Instead, it should
    // fail gracefully with a type error like all the other data type
    // definitions do.
    // $fails = $filter->validateArguments($data->getDataDefinition(), [new \stdClass()]);
    $fails = $filter->validateArguments($data->getDataDefinition(), ['123']);
    $this->assertEquals(1, count($fails));
    $this->assertEquals('This value should be of the correct primitive type.', $fails[0]);

    $this->assertEquals('<a href="https://www.example.com/">link text</a>', $filter->filter($data->getDataDefinition(), $data->getValue(), ['link text']));
  }

  /**
   * Tests the operation of the 'trim' data filter.
   *
   * @covers \Drupal\tr_rulez\Plugin\TypedDataFilter\TrimFilter
   */
  public function testTrimFilter() {
    $filter = $this->dataFilterManager->createInstance('trim');
    $data = $this->typedDataManager->create(DataDefinition::create('string'), ' Text with whitespace ');

    $this->assertTrue($filter->canFilter($data->getDataDefinition()));
    $this->assertFalse($filter->canFilter(DataDefinition::create('any')));

    $this->assertEquals('string', $filter->filtersTo($data->getDataDefinition(), [])->getDataType());

    $this->assertEquals('Text with whitespace', $filter->filter($data->getDataDefinition(), $data->getValue(), []));
  }

  /**
   * Tests the operation of the 'replace' data filter.
   *
   * @covers \Drupal\tr_rulez\Plugin\TypedDataFilter\ReplaceFilter
   */
  public function testReplaceFilter() {
    $filter = $this->dataFilterManager->createInstance('replace');
    $data = $this->typedDataManager->create(DataDefinition::create('string'), 'Text with mispeling to correct');

    $this->assertTrue($filter->canFilter($data->getDataDefinition()));
    $this->assertFalse($filter->canFilter(DataDefinition::create('any')));

    $this->assertEquals('string', $filter->filtersTo($data->getDataDefinition(), [])->getDataType());

    // Verify that two arguments are required.
    $fails = $filter->validateArguments($data->getDataDefinition(), [/* No arguments given */]);
    $this->assertEquals(1, count($fails));
    $this->assertContains('Missing arguments for filter', (string) $fails[0]);

    $fails = $filter->validateArguments($data->getDataDefinition(), [new \stdClass(), new \stdClass()]);
    $this->assertEquals(2, count($fails));
    $this->assertEquals('This value should be of the correct primitive type.', $fails[0]);
    $this->assertEquals('This value should be of the correct primitive type.', $fails[1]);

    $this->assertEquals('Text with misspelling to correct', $filter->filter($data->getDataDefinition(), $data->getValue(), ['mispeling', 'misspelling']));
  }

  /**
   * Tests the operation of the 'raw' data filter.
   *
   * @covers \Drupal\tr_rulez\Plugin\TypedDataFilter\RawFilter
   */
  public function testRawFilter() {
    $filter = $this->dataFilterManager->createInstance('raw');
    $data = $this->typedDataManager->create(DataDefinition::create('string'), '<b>Test <em>raw</em> filter</b>');

    $this->assertTrue($filter->canFilter($data->getDataDefinition()));
    $this->assertFalse($filter->canFilter(DataDefinition::create('any')));

    $this->assertEquals('string', $filter->filtersTo($data->getDataDefinition(), [])->getDataType());

    $this->assertEquals('<b>Test <em>raw</em> filter</b>', $filter->filter($data->getDataDefinition(), $data->getValue(), []));
  }

  /**
   * Tests the operation of the 'count' data filter.
   *
   * @covers \Drupal\tr_rulez\Plugin\TypedDataFilter\CountFilter
   */
  public function testCountFilter() {
    $filter = $this->dataFilterManager->createInstance('count');
    $data = $this->typedDataManager->create(DataDefinition::create('string'), 'No one shall speak to the Man at the Helm.');

    $this->assertTrue($filter->canFilter($data->getDataDefinition()));
    $this->assertFalse($filter->canFilter(DataDefinition::create('any')));

    $this->assertEquals('integer', $filter->filtersTo($data->getDataDefinition(), [])->getDataType());

    $this->assertEquals(42, $filter->filter($data->getDataDefinition(), $data->getValue(), []));
  }

  /**
   * @covers \Drupal\tr_rulez\Plugin\TypedDataFilter\UpperFilter
   */
  public function testUpperFilter() {
    $filter = $this->dataFilterManager->createInstance('upper');
    $data = $this->typedDataManager->create(DataDefinition::create('string'), 'tEsT');

    $this->assertTrue($filter->canFilter($data->getDataDefinition()));
    $this->assertFalse($filter->canFilter(DataDefinition::create('any')));

    $this->assertEquals('string', $filter->filtersTo($data->getDataDefinition(), [])->getDataType());

    $this->assertEquals('TEST', $filter->filter($data->getDataDefinition(), $data->getValue(), []));
  }

}
