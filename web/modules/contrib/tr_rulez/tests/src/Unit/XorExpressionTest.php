<?php

namespace Drupal\Tests\tr_rulez\Unit;

use Drupal\Tests\rules\Unit\RulesUnitTestBase;
use Drupal\rules\Context\ExecutionStateInterface;
use Drupal\rules\Engine\ConditionExpressionInterface;
use Drupal\tr_rulez\Plugin\RulesExpression\XorExpression;
use Prophecy\Argument;

/**
 * @coversDefaultClass \Drupal\tr_rulez\Plugin\RulesExpression\XorExpression
 * @group tr_rulez
 */
class XorExpressionTest extends RulesUnitTestBase {

  /**
   * The 'xor' condition container being tested.
   *
   * @var \Drupal\rules\Engine\ConditionExpressionContainerInterface
   */
  protected $xor;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->xor = new XorExpression([], '', ['label' => 'Condition set (XOR)'], $this->expressionManager->reveal(), $this->rulesDebugLogger->reveal());
  }

  /**
   * Tests one condition.
   */
  public function testOneCondition() {
    // The method on the test condition must be called once.
    $this->trueConditionExpression->executeWithState(
      Argument::type(ExecutionStateInterface::class))->shouldBeCalledTimes(1);

    $this->xor->addExpressionObject($this->trueConditionExpression->reveal());
    $this->assertTrue($this->xor->execute(), 'Single TRUE condition returns TRUE.');
  }

  /**
   * Tests an empty XOR.
   */
  public function testEmptyXor() {
    $property = new \ReflectionProperty($this->xor, 'conditions');
    $property->setAccessible(TRUE);

    $this->assertEmpty($property->getValue($this->xor));
    $this->assertFalse($this->xor->execute(), 'Empty XOR returns FALSE.');
  }

  /**
   * Tests two true conditions.
   */
  public function testTwoConditions() {
    // The method on the test condition must be called once.
    $this->trueConditionExpression->executeWithState(
      Argument::type(ExecutionStateInterface::class))->shouldBeCalledTimes(1);

    $second_condition = $this->prophesize(ConditionExpressionInterface::class);
    $second_condition->getUuid()->willReturn('true_uuid2');
    $second_condition->getWeight()->willReturn(0);
    $second_condition->executeWithState(Argument::type(ExecutionStateInterface::class))
      ->willReturn(TRUE)
      ->shouldBeCalledTimes(1);

    $this->xor
      ->addExpressionObject($this->trueConditionExpression->reveal())
      ->addExpressionObject($second_condition->reveal());

    $this->assertFalse($this->xor->execute(), 'Two TRUE conditions returns FALSE.');
  }

  /**
   * Tests two false conditions.
   */
  public function testTwoFalseConditions() {
    // The method on the test condition must be called once.
    $this->falseConditionExpression->executeWithState(
      Argument::type(ExecutionStateInterface::class))->shouldBeCalledTimes(1);

    $second_condition = $this->prophesize(ConditionExpressionInterface::class);
    $second_condition->getUuid()->willReturn('false_uuid2');
    $second_condition->getWeight()->willReturn(0);
    $second_condition->executeWithState(Argument::type(ExecutionStateInterface::class))
      ->willReturn(FALSE)
      ->shouldBeCalledTimes(1);

    $this->xor
      ->addExpressionObject($this->falseConditionExpression->reveal())
      ->addExpressionObject($second_condition->reveal());

    $this->assertFalse($this->xor->execute(), 'Two FALSE conditions return FALSE.');
  }

  /**
   * Tests one true, one false condition.
   */
  public function testOneTrueOneFalseCondition() {
    // The method on the test condition must be called once.
    $this->trueConditionExpression->executeWithState(
      Argument::type(ExecutionStateInterface::class))->shouldBeCalledTimes(1);

    $second_condition = $this->prophesize(ConditionExpressionInterface::class);
    $second_condition->getUuid()->willReturn('false_uuid2');
    $second_condition->getWeight()->willReturn(0);
    $second_condition->executeWithState(Argument::type(ExecutionStateInterface::class))
      ->willReturn(FALSE)
      ->shouldBeCalledTimes(1);

    $this->xor
      ->addExpressionObject($this->trueConditionExpression->reveal())
      ->addExpressionObject($second_condition->reveal());

    $this->assertTrue($this->xor->execute(), 'One FALSE, one TRUE condition returns TRUE.');
  }

  /**
   * Tests odd number of true conditions.
   */
  public function testOddEvenTrueConditions() {
    // The method on the test condition must be called once.
    $this->trueConditionExpression->executeWithState(
      Argument::type(ExecutionStateInterface::class))->shouldBeCalledTimes(2);

    $second_condition = $this->prophesize(ConditionExpressionInterface::class);
    $second_condition->getUuid()->willReturn('false_uuid2');
    $second_condition->getWeight()->willReturn(0);
    $second_condition->executeWithState(Argument::type(ExecutionStateInterface::class))
      ->willReturn(FALSE)
      ->shouldBeCalledTimes(2);

    $third_condition = $this->prophesize(ConditionExpressionInterface::class);
    $third_condition->getUuid()->willReturn('false_uuid3');
    $third_condition->getWeight()->willReturn(0);
    $third_condition->executeWithState(Argument::type(ExecutionStateInterface::class))
      ->willReturn(FALSE)
      ->shouldBeCalledTimes(2);

    $fourth_condition = $this->prophesize(ConditionExpressionInterface::class);
    $fourth_condition->getUuid()->willReturn('true_uuid4');
    $fourth_condition->getWeight()->willReturn(0);
    $fourth_condition->executeWithState(Argument::type(ExecutionStateInterface::class))
      ->willReturn(TRUE)
      ->shouldBeCalledTimes(1);

    $this->xor
      ->addExpressionObject($this->trueConditionExpression->reveal())
      ->addExpressionObject($second_condition->reveal())
      ->addExpressionObject($third_condition->reveal());
    $this->assertTrue($this->xor->execute(), 'Odd number of TRUE conditions returns TRUE.');

    $this->xor->addExpressionObject($fourth_condition->reveal());
    $this->assertFalse($this->xor->execute(), 'Even number of TRUE conditions returns FALSE.');
  }

}
