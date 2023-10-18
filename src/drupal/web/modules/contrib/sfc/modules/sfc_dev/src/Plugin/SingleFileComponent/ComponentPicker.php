<?php

namespace Drupal\sfc_dev\Plugin\SingleFileComponent;

use Drupal\sfc\ComponentBase;

/**
 * Renders a component picker for the library.
 *
 * @SingleFileComponent(
 *   id = "sfc_dev_component_picker",
 * )
 */
class ComponentPicker extends ComponentBase {

  const TEMPLATE = <<<TWIG
<div class="component-picker">
  <div class="component-picker__header">{{ 'Components' | t }}</div>
  <ul class="component-picker__list">
  {% for group,definitions in grouped_definitions  %}
      <li>
        <button class="component-picker__group js-component-picker-group" aria-expanded="false">{{ group }}</button>
        <ul class="component-picker__list">
          {% for id,definition in definitions  %}
          <li class="component-picker__link">
              <a href="{{ path('sfc_dev.library_preview', {plugin_id: id}) }}" class="use-ajax" data-component-picker-id="{{ id }}">{{ id }}</a>
          </li>
          {% endfor %}
        </ul>
      </li>
  {% endfor %}
  </ul>
</div>
TWIG;

  const CSS = <<<CSS
.component-picker {
    line-height: 1.5;
    min-width: 200px;
    background: var(--component-library-dark-bg);
    min-height: 500px;
    color: var(--component-library-light-text, white);
    font-family: var(--component-library-font-family);
    height: 100%;
}
.component-picker__header {
    font-size: 14px;
    padding: 10px 15px;
    background: var(--component-library-dark-bg-hover);
}
.component-picker__list {
    margin: 0;
    list-style: none;
    padding: 0;
}
.component-picker__group {
    color: white;
    cursor: pointer;
    padding: 10px 15px;
    font-size: 14px;
    background: var(--component-library-dark-bg-alt);
    display: block;
    width: 100%;
    text-align: left;
    border: 0;
    transition: .2s background;
}
.component-picker__group:hover {
    background: var(--component-library-dark-bg-hover);
}
.component-picker__group:after {
    content: "-";
    float: right;
    font-family: monospace;
    margin-left: 10px;
}
.component-picker__group.collapsed:after {
    content: "+";
}
.component-picker__link a {
    color: var(--component-library-light-text);
    border: none;
    text-decoration: none;
    padding: 10px 15px;
    font-size: 14px;
    display: block;
    background: var(--component-library-dark-bg);
    outline-offset: 0;
    transition: .2s background;
}
.component-picker__link a:hover,
.component-picker__link a:active,
.component-picker__link a:focus,
.component-picker__link a.active {
    background: var(--component-library-dark-bg-hover);
    color: var(--component-library-light-text);
    border: none;
    text-decoration: none;
}
CSS;

  const SELECTOR = '.component-picker';

  const ATTACH = <<<JS
$(this).find('.js-component-picker-group').on('click', function () {
  $(this).siblings('ul').toggle();
  $(this).toggleClass('collapsed');
  if ($(this).hasClass('collapsed')) {
    $(this).attr('aria-expanded', 'false');
  }
  else {
    $(this).attr('aria-expanded', 'true');
  }
});
JS;

  const DEPENDENCIES = [
    'core/drupal.ajax',
  ];

}
