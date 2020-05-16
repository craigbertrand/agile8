<?php

namespace Drupal\tr_rulez\Plugin\TypedDataFilter;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Link;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\Markup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\Type\UriInterface;
use Drupal\Core\Url;
use Drupal\typed_data\DataFilterBase;

/**
 * A data filter which generates an HTML anchor from a URL.
 *
 * @DataFilter(
 *   id = "link",
 *   label = @Translation("The link filter turns uri data into an HTML anchor element."),
 * )
 */
class LinkFilter extends DataFilterBase {

  /**
   * {@inheritdoc}
   */
  public function canFilter(DataDefinitionInterface $definition) {
    return is_subclass_of($definition->getClass(), UriInterface::class);
  }

  /**
   * {@inheritdoc}
   */
  public function filtersTo(DataDefinitionInterface $definition, array $arguments) {
    return DataDefinition::create('string');
  }

  /**
   * {@inheritdoc}
   */
  public function getNumberOfRequiredArguments() {
    return 1;
  }

  /**
   * {@inheritdoc}
   */
  public function validateArguments(DataDefinitionInterface $definition, array $arguments) {
    $errors = parent::validateArguments($definition, $arguments);
    if (isset($arguments[0])) {
      // Ensure the provided value is given for this data.
      $violations = $this->getTypedDataManager()
        ->create($definition, $arguments[0])
        ->validate();
      foreach ($violations as $violation) {
        $errors[] = $violation->getMessage();
      }
    }
    return $errors;
  }

  /**
   * {@inheritdoc}
   */
  public function filter(DataDefinitionInterface $definition, $value, array $arguments, BubbleableMetadata $bubbleable_metadata = NULL) {
    $value = Link::fromTextAndUrl(Xss::filterAdmin($arguments[0]), Url::fromUri($value))->toString();
    return Markup::create($value);
  }

}
