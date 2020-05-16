<?php

namespace Drupal\Tests\tr_rulez\Unit\Integration\Condition;

use Drupal\Core\Path\CurrentPathStack;
use Drupal\Tests\rules\Unit\Integration\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\tr_rulez\Plugin\Condition\PathTextComparison
 * @group RulesCondition
 */
class PathTextComparisonTest extends RulesIntegrationTestBase {

  /**
   * The mocked current path stack service.
   *
   * @var \Drupal\Core\Path\CurrentPathStack|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $currentPathStack;

  /**
   * The condition to be tested.
   *
   * @var \Drupal\rules\Core\RulesConditionInterface
   */
  protected $condition;

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

    // Mock the current path stack.
    $this->currentPathStack = $this->prophesize(CurrentPathStack::class);
    $this->container->set('path.current', $this->currentPathStack->reveal());
    $this->currentPathStack->getPath()->willReturn('some/random/test/path');

    // Load the plugin to test.
    $this->condition = $this->conditionManager->createInstance('rules_path_text_comparison');
  }

  /**
   * Tests evaluating the condition with the "starts" operator.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluationOperatorStarts() {
    // Test that when the path string ('some/random/test/path') starts with the
    // match string and the operator is 'starts', TRUE is returned.
    $this->condition
      ->setContextValue('operator', 'starts')
      ->setContextValue('match', 'some');
    $this->assertTrue($this->condition->evaluate());

    // Test that when the path string ('some/random/test/path') does not start
    // with the match string and the operator is 'starts', FALSE is returned.
    $this->condition
      ->setContextValue('operator', 'starts')
      ->setContextValue('match', 'awe-some');
    $this->assertFalse($this->condition->evaluate());
  }

  /**
   * Tests evaluating the condition with the "ends" operator.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluationOperatorEnds() {
    // Test that when the path string ('some/random/test/path') ends with the
    // match string and the operator is 'ends', TRUE is returned.
    $this->condition
      ->setContextValue('operator', 'ends')
      ->setContextValue('match', 'path');
    $this->assertTrue($this->condition->evaluate());

    // Test that when the path string ('some/random/test/path') does not end
    // with the match string and the operator is 'ends', FALSE is returned.
    $this->condition
      ->setContextValue('operator', 'ends')
      ->setContextValue('match', 'a-path-y');
    $this->assertFalse($this->condition->evaluate());
  }

  /**
   * Tests evaluating the condition with the "contains" operator.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluationOperatorContains() {
    // Test that when the path string ('some/random/test/path') contains the
    // match string and the operator is 'contains', TRUE is returned.
    $this->condition
      ->setContextValue('operator', 'contains')
      ->setContextValue('match', 'random');
    $this->assertTrue($this->condition->evaluate());

    // Test that when the path string ('some/random/test/path') does not
    // contain the match string and the operator is 'contains', FALSE is
    // returned.
    $this->condition
      ->setContextValue('operator', 'contains')
      ->setContextValue('match', 'modnar');
    $this->assertFalse($this->condition->evaluate());
  }

  /**
   * Tests evaluating the condition with the "regex" operator.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluationOperatorRegex() {
    // Test that when the operator is 'regex' and the regular expression in the
    // match string matches the path string('some/random/test/path'),
    // TRUE is returned.
    $this->condition
      ->setContextValue('operator', 'regex')
      ->setContextValue('match', 'randd?om.*patt?h');
    $this->assertTrue($this->condition->evaluate());

    // Test that when the operator is 'regex' and the regular expression in the
    // match string does not match the path string('some/random/test/path'),
    // FALSE is returned.
    $this->condition
      ->setContextValue('operator', 'regex')
      ->setContextValue('match', 'randd+om.*patt?h');
    $this->assertFalse($this->condition->evaluate());
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Path text comparison', $this->condition->summary());
  }

}
