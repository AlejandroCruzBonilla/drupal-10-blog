<?php

namespace Drupal\sfc_test\Plugin\SingleFileComponent;

use Drupal\sfc\ComponentBase;

/**
 * Contains an example component that acts as a field formatter.
 *
 * @SingleFileComponent(
 *   id = "multiple_formatter",
 *   field_formatter = {
 *     "label" = "Bold text",
 *     "description" = "Makes plain text bold",
 *     "field_types" = {"string"},
 *     "sfc_multiple" = true
 *   }
 * )
 *
 * @codeCoverageIgnore
 */
class MultipleFormatter extends ComponentBase {

  const TEMPLATE = <<<TWIG
<ul class="multiple-formatter">
{% for item in items %}
  <li>{{ item.getValue().value }}</li>
{% endfor %}
</ul>
TWIG;

}
