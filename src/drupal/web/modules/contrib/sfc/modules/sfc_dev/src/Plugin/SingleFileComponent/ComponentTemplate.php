<?php

namespace Drupal\sfc_dev\Plugin\SingleFileComponent;

use Drupal\sfc\ComponentBase;

/**
 * Renders the template for a component, for use in the library.
 *
 * @SingleFileComponent(
 *   id = "sfc_dev_component_template",
 * )
 */
class ComponentTemplate extends ComponentBase {

  const TEMPLATE = <<<TWIG
<div class="component-template">
  <div class="component-template__code">{{ code }}</div>
  <div class="component-template__filename">
      {% trans %}Located at {{ filename }}{% endtrans %}
  </div>
</div>
TWIG;

  const CSS = <<<CSS
.component-template {
    font-size: 12px;
    line-height: 1.5;
}
.component-template__code {
    overflow: scroll;
    font-family: monospace, monospace;
    white-space: pre;
    padding: 10px;
    background: #f9f9f9;
    color: black;
    margin-bottom: 15px;
    border-bottom: 2px solid gray;
}
CSS;

  /**
   * {@inheritdoc}
   */
  public function prepareContext(array &$context) {
    if (isset($context['component'])) {
      $obj = new \ReflectionClass($context['component']);
      $context['filename'] = ltrim(str_replace(DRUPAL_ROOT, '', $obj->getFileName()), '/');
      $context['code'] = preg_replace('/({# End - ComponentBase additions #})/', "$1\n", $context['component']->getTemplate());
    }
  }

}
