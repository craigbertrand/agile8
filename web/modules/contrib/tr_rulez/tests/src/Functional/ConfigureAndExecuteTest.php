<?php

namespace Drupal\Tests\tr_rulez\Functional;

use Drupal\Tests\rules\Functional\RulesBrowserTestBase;

/**
 * Tests that a rule can be configured and triggered when a node is edited.
 *
 * @group tr_rulez
 */
class ConfigureAndExecuteTest extends RulesBrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['node', 'rules', 'tr_rulez'];

  /**
   * We use the minimal profile because we want to test local action links.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   * A user account with administration permissions.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create an article content type that we will use for testing.
    $type = $this->container->get('entity_type.manager')->getStorage('node_type')
      ->create([
        'type' => 'article',
        'name' => 'Article',
      ]);
    $type->save();

    $this->account = $this->drupalCreateUser([
      'create article content',
      'administer rules',
      'administer site configuration',
    ]);
  }

  /**
   * Tests creation of a rule and then triggering its execution.
   */
  public function testConfigureAndExecute() {
    $this->drupalLogin($this->account);

    $this->drupalGet('admin/config/workflow/rules');

    // Set up a rule that will show a system message if the title of a node
    // matches "Test title".
    $this->clickLink('Add reaction rule');

    $this->fillField('Label', 'Test rule');
    $this->fillField('Machine-readable name', 'test_rule');
    $this->fillField('React on event', 'rules_entity_presave:node');
    $this->pressButton('Save');

    $this->clickLink('Add condition');

    $this->fillField('Condition', 'rules_data_comparison');
    $this->pressButton('Continue');

    $this->fillField('context_definitions[data][setting]', 'node.title.0.value');
    $this->fillField('context_definitions[value][setting]', 'Test title');
    $this->pressButton('Save');

    $this->clickLink('Add action');
    $this->fillField('Action', 'rules_system_message');
    $this->pressButton('Continue');

    $this->fillField('context_definitions[message][setting]', 'Title matched "Test title"!');
    $this->fillField('context_definitions[type][setting]', 'status');
    $this->pressButton('Save');

    // One more save to permanently store the rule.
    $this->pressButton('Save');

    /** @var \Drupal\Tests\WebAssert $assert */
    $assert = $this->assertSession();

    // Add a node now and check if our rule triggers.
    $this->drupalGet('node/add/article');
    $this->fillField('Title', 'Test title');
    $this->pressButton('Save');
    $assert->pageTextContains('Title matched "Test title"!');

    // Disable rule and make sure it doesn't get triggered.
    $this->drupalGet('admin/config/workflow/rules');
    $this->clickLink('Disable');

    $this->drupalGet('node/add/article');
    $this->fillField('Title', 'Test title');
    $this->pressButton('Save');
    $assert->pageTextNotContains('Title matched "Test title"!');

    // Re-enable the rule and make sure it gets triggered again.
    $this->drupalGet('admin/config/workflow/rules');
    $this->clickLink('Enable');

    $this->drupalGet('node/add/article');
    $this->fillField('Title', 'Test title');
    $this->pressButton('Save');
    $assert->pageTextContains('Title matched "Test title"!');

    // Edit the rule and negate the condition.
    $this->drupalGet('admin/config/workflow/rules/reactions/edit/test_rule');
    $this->clickLink('Edit', 0);
    $this->getSession()->getPage()->checkField('negate');
    $this->pressButton('Save');
    // One more save to permanently store the rule.
    $this->pressButton('Save');

    // Need to clear cache so that the edited version will be used.
    // @todo Can this be removed when
    // https://www.drupal.org/project/rules/issues/2769511 is fixed?
    drupal_flush_all_caches();

    // Create node with same title and check that the message is not shown.
    $this->drupalGet('node/add/article');
    $this->fillField('Title', 'Test title');
    $this->pressButton('Save');
    $assert->pageTextNotContains('Title matched "Test title"!');
  }

  /**
   * Tests user input in context form for 'multiple' valued context variables.
   */
  public function testMultipleInputContext() {
    $this->drupalLogin($this->account);

    $this->drupalGet('admin/config/workflow/rules');

    // Set up a rule that will check the node type of a newly-created node.
    // The node type is the 'multiple' valued textarea we will test.
    $this->clickLink('Add reaction rule');

    $this->fillField('Label', 'Test rule');
    $this->fillField('Machine-readable name', 'test_rule');
    $this->fillField('React on event', 'rules_entity_insert:node');
    $this->pressButton('Save');

    $this->clickLink('Add condition');

    // Use node_is_of_type because the types field has 'multiple = TRUE'.
    $this->fillField('Condition', 'rules_node_is_of_type');
    $this->pressButton('Continue');

    $this->fillField('context_definitions[node][setting]', 'node');

    $suboptimal_user_input = [
      "  \r\nwhitespace at beginning of input\r\n",
      "text\r\n",
      "trailing space  \r\n",
      "\rleading terminator\r\n",
      "  leading space\r\n",
      "multiple words, followed by primitive values\r\n",
      "0\r\n",
      "0.0\r\n",
      "128\r\n",
      " false\r\n",
      "true \r\n",
      "null\r\n",
      "terminator r\r",
      "two empty lines\n\r\n\r",
      "terminator n\n",
      "terminator nr\n\r",
      "whitespace at end of input\r\n        \r\n",
    ];
    $this->fillField('context_definitions[types][setting]', implode($suboptimal_user_input));
    $this->pressButton('Save');

    // One more save to permanently store the rule.
    $this->pressButton('Save');

    // Now examine the config to ensure the user input was parsed properly
    // and that blank lines, leading and trailing whitespace, and wrong line
    // terminators were removed.
    $expected_config_value = [
      "whitespace at beginning of input",
      "text",
      "trailing space",
      "leading terminator",
      "leading space",
      "multiple words, followed by primitive values",
      "0",
      "0.0",
      "128",
      "false",
      "true",
      "null",
      "terminator r",
      "two empty lines",
      "terminator n",
      "terminator nr",
      "whitespace at end of input",
    ];
    $config_factory = $this->container->get('config.factory');
    $rule = $config_factory->get('rules.reaction.test_rule');
    $this->assertEquals($expected_config_value, $rule->get('expression.conditions.conditions.0.context_values.types'));
  }

}
