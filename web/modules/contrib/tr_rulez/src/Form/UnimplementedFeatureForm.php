<?php

namespace Drupal\tr_rulez\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Notification of a missing UI feature, intended to display in a modal dialog.
 */
class UnimplementedFeatureForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tr_rulez_unimplemented_feature_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $feature = NULL, $title = NULL, $issue = NULL) {
    $form['message'] = [
      '#prefix' => '<p>',
      '#markup' => $this->t('The User Interface element %feature is not yet operational. Although the code exists to support this, the UI is incomplete. If you would like to help get this feature implemented please participate in <a href=":issue">@title</a>', [
        '%feature' => $feature,
        '@title' => '#' . $issue . ': ' . $title,
        ':issue' => Url::fromUri('https://www.drupal.org/node/' . $issue)->toString(),
      ]),
      '#suffix' => '</p>',
    ];

    // Attach the library for pop-up dialogs/modals.
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // No submit handler needed.
  }

}
