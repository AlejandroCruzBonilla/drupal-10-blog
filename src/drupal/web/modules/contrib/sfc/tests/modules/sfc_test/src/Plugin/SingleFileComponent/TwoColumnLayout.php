<?php

namespace Drupal\sfc_test\Plugin\SingleFileComponent;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\sfc\LayoutComponentBase;

/**
 * Contains an example component that provides a layout.
 *
 * @SingleFileComponent(
 *   id = "two_column_flexible",
 *   group = "Example",
 *   layout = {
 *     "label" = "Two column (flexible)",
 *     "category" = "Example components",
 *     "regions" = {
 *       "left" = {"label" = "Left"},
 *       "right" = {"label" = "Right"},
 *     },
 *     "icon_map" = {{"left", "right"}},
 *   }
 * )
 *
 * @codeCoverageIgnore
 */
class TwoColumnLayout extends LayoutComponentBase {

  const TEMPLATE = <<<TWIG
<div{{ attributes.addClass('two-column', sizing_class) }}>
  <div{{ region_attributes.left.addClass('left') }}>
    {{ content.left }}
  </div>
  <div{{ region_attributes.right.addClass('right') }}>
    {{ content.right }}
  </div>
</div>
TWIG;

  const CSS = <<<CSS
.two-column {
  display: flex;
}
.two-column .left {
  margin-right: 10px;
}
.two-column.sizing-30-70 .right,
.two-column.sizing-70-30 .left {
  flex-basis: 70%
}
CSS;

  /**
   * {@inheritdoc}
   */
  public function buildContextForm(array $form, FormStateInterface $form_state, array $default_values = []) {
    $form['sizing'] = [
      '#type' => 'select',
      '#title' => $this->t('Sizing'),
      '#description' => $this->t('The sizing for the columns.'),
      '#options' => [
        '50-50' => '50/50',
        '30-70' => '30/70',
        '70-30' => '70-30',
      ],
      '#required' => TRUE,
      '#default_value' => isset($default_values['sizing']) ? $default_values['sizing'] : '50-50',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareContext(array &$context) {
    parent::prepareContext($context);
    $sizing = isset($context['sizing']) ? $context['sizing'] : '50-50';
    $context['sizing_class'] = Html::cleanCssIdentifier('sizing-' . $sizing);
  }

}
