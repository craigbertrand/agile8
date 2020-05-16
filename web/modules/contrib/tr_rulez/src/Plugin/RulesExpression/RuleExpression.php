<?php

namespace Drupal\tr_rulez\Plugin\RulesExpression;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\rules\Context\ExecutionStateInterface;
use Drupal\rules\Engine\ExpressionManagerInterface;
use Drupal\rules\Plugin\RulesExpression\RuleExpression as CoreRuleExpression;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a rule, executing actions when conditions are met.
 *
 * Actions added to a rule can also be rules themselves, so it is possible to
 * nest several rules into one rule. This is the functionality of so called
 * "rule sets" in Drupal 7.
 *
 * @RulesExpression(
 *   id = "rules_rule",
 *   label = @Translation("Rule"),
 *   description = @Translation("The Rule component provides a rule, which is a set of conditions and actions. The actions are executed when the conditions are met. Actions added to a rule can also be rules themselves, so it is possible to nest several rules into one rule. This is the functionality of so called 'rule sets' in Drupal 7."),
 *   form_class = "\Drupal\rules\Form\Expression\RuleExpressionForm"
 * )
 */
class RuleExpression extends CoreRuleExpression {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\rules\Engine\ExpressionManagerInterface $expression_manager
   *   The rules expression plugin manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The Rules debug logger channel.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ExpressionManagerInterface $expression_manager, EntityTypeManagerInterface $entity_type_manager, LoggerChannelInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $expression_manager, $logger);
    $this->entityTypeManager = $entity_type_manager;
    $this->rulesDebugLogger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.rules_expression'),
      $container->get('entity_type.manager'),
      $container->get('logger.channel.rules_debug')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function executeWithState(ExecutionStateInterface $state) {
    $storage = $this->entityTypeManager->getStorage('rules_reaction_rule');
    // Check whether the reaction rule is enabled.
    $rule_is_enabled = $storage->loadByProperties([
      'expression.uuid' => $this->uuid,
      'status' => TRUE,
    ]);
    if (empty($rule_is_enabled)) {
      // The reaction rule is disabled so do not run it.
      return;
    }
    $this->rulesDebugLogger->info('Evaluating conditions of rule %label.', [
      '%label' => $this->getLabel(),
      'element' => $this,
    ]);
    // Evaluate the rule's conditions.
    if (!$this->conditions->isEmpty() && !$this->conditions->executeWithState($state)) {
      // Do not run the actions if the conditions are not met.
      return;
    }

    $this->rulesDebugLogger->info('Rule %label fires.', [
      '%label' => $this->getLabel(),
      'element' => $this,
      'scope' => TRUE,
    ]);
    $this->actions->executeWithState($state);
    $this->rulesDebugLogger->info('Rule %label has fired.', [
      '%label' => $this->getLabel(),
      'element' => $this,
      'scope' => FALSE,
    ]);
  }

}
