<?php

namespace Drupal\sfc_test\Plugin\SingleFileComponent;

use Drupal\Core\Form\FormStateInterface;
use Drupal\sfc\ComponentBase;

/**
 * Contains an example component that acts as a field formatter.
 *
 * @SingleFileComponent(
 *   id = "bold_formatter",
 *   group = "Example",
 *   field_formatter = {
 *     "label" = "Bold text",
 *     "description" = "Makes plain text bold",
 *     "field_types" = {"string"}
 *   }
 * )
 *
 * @codeCoverageIgnore
 */
class BoldFormatter extends ComponentBase {

  const TEMPLATE = <<<TWIG
<div class="bold-formatter">{{ text }}</div>
TWIG;

  /**
   * {@inheritdoc}
   */
  public function prepareContext(array &$context) {
    if (isset($context['item']) && !isset($context['text'])) {
      $context['text'] = $context['item']->value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildContextForm(array $form, FormStateInterface $form_state, array $default_values = []) {
    $form['text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text'),
      '#default_value' => isset($default_values['text']) ? $default_values['text'] : '',
    ];
    return $form;
  }

}
