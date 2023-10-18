<?php

namespace Drupal\sfc_test\Plugin\SingleFileComponent;

use Drupal\sfc\ComponentBase;

/**
 * Contains an example single file component.
 *
 * @SingleFileComponent(
 *   id = "js_render"
 * )
 *
 * @codeCoverageIgnore
 */
class JSRender extends ComponentBase {

  const TEMPLATE = <<<TWIG
<div class="js-js-render"></div>
TWIG;

  /**
   * {@inheritdoc}
   */
  protected function hasAttachments() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function getAttachmentData() {
    return [
      'selector' => '.js-js-render',
      'attach' => \Drupal::keyValue('sfc_test')->get('js_render', '$(this).text("JS Render")'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function shouldWriteAssets() {
    return parent::shouldWriteAssets() || \Drupal::keyValue('sfc_test')->get('js_render_should_write', FALSE);
  }

}
