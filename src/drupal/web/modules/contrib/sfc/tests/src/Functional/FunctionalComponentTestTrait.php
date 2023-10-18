<?php

namespace Drupal\Tests\sfc\Functional;

/**
 * Contains useful methods for testing single file components in the browser.
 *
 * You must enable the sfc_test module to use this trait.
 *
 * @codeCoverageIgnore
 */
trait FunctionalComponentTestTrait {

  /**
   * Visits a page with the given component rendered and ready for interaction.
   *
   * @param string $plugin_id
   *   The component plugin ID.
   * @param array $context
   *   The template context.
   */
  protected function visitComponent($plugin_id, array $context) {
    \Drupal::keyValue('sfc_test')->set('sfc_test_context', serialize($context));
    $this->drupalGet('/sfc_test/render_component/' . $plugin_id);
  }

}
