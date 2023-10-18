<?php

namespace Drupal\sfc_test\Plugin\SingleFileComponent;

use Drupal\Core\Form\FormStateInterface;
use Drupal\sfc\ComponentBase;

/**
 * Contains an example single file component.
 *
 * @SingleFileComponent(
 *   id = "say_hello",
 *   block = {
 *     "admin_label" = "Say hello",
 *   }
 * )
 *
 * @codeCoverageIgnore
 */
class SayHello extends ComponentBase {

  const TEMPLATE = <<<TWIG
<p class="say-hello">Hello {{ name }}!</p>
TWIG;

  const CSS = <<<CSS
.say-hello {
  color: pink;
}
CSS;

  const JS = <<<JS
console.log('hi');
JS;

  /**
   * {@inheritdoc}
   */
  public function buildContextForm(array $form, FormStateInterface $form_state, array $default_values = []) {
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateContextForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('name') === 'Bob') {
      $form_state->setError($form['name'], $this->t('Bob is not an allowed name.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitContextForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('name') === 'friend') {
      $form_state->setValue('name', 'my friend');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function prepareContext(array &$context) {
    if (!isset($context['name'])) {
      $context['name'] = \Drupal::currentUser()->getDisplayName();
    }
  }

}
