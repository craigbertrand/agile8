<?php

namespace Drupal\tr_rulez\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\rules\Entity\ReactionRuleConfig;

/**
 * Controller methods for Reaction rules.
 */
class RulesReactionController extends ControllerBase {

  /**
   * Clones a reaction rule.
   *
   * @param \Drupal\rules\Entity\ReactionRuleConfig $rules_reaction_rule
   *   The reaction rule configuration entity.
   */
  public function saveClone(ReactionRuleConfig $rules_reaction_rule) {
    $name = $rules_reaction_rule->label();

    // Tweak the name and unset the ID.
    $cloned_rule = $rules_reaction_rule->createDuplicate();
    $cloned_rule->set('label', $this->t('Copy of @name', ['@name' => $name]));
    // @todo Have to check for uniqueness of name first - in case we have
    // cloned this rule before ...
    $cloned_rule->set('id', $rules_reaction_rule->id() . "_clone");

    // Save the new rule.
    $cloned_rule->save();

    // Display a message and redirect back to the methods page.
    $this->messenger()->addMessage($this->t('Reaction rule %name was cloned.', ['%name' => $name]));

    return $this->redirect('entity.rules_reaction_rule.collection');
  }

}
