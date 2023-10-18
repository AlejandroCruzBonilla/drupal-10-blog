<?php

namespace Drupal\sfc_dev\Plugin\SingleFileComponent;

use Drupal\sfc\ComponentBase;

/**
 * Renders a component library user interface given a set of components.
 *
 * @SingleFileComponent(
 *   id = "sfc_dev_component_library",
 * )
 */
class ComponentLibrary extends ComponentBase {

  const TEMPLATE = <<<TWIG
<div class="component-library">
  <div class="component-library__picker">
      {% include "sfc--sfc-dev-component-picker.html.twig" %}
  </div>
  <div class="component-library__preview">
      {% include "sfc--sfc-dev-component-preview.html.twig" %}
  </div>
</div>
TWIG;

  const CSS = <<<CSS
.component-library {
    display: flex;
    margin-bottom: 15px;
    --component-library-dark-bg: #0277BD;
    --component-library-dark-bg-hover: #01579B;
    --component-library-dark-bg-alt: #0288D1;
    --component-library-light-text: white;
    --component-library-font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Oxygen", "Ubuntu", "Helvetica Neue", Arial, sans-serif;
}
.component-library .ajax-progress {
    display: none;
}
.component-library__preview {
    flex-grow: 1;
    background: white;
}
CSS;

}
