<?php

namespace Drupal\sfc\Element;

use Drupal\Core\Render\Element\RenderElement;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\sfc\ComponentNameHelper;

/**
 * Provides a render element for rendering components.
 *
 * Basically just wraps the inline template element for ease of use.
 *
 * Properties:
 * - #id: The component ID.
 * - #context: (array) The variables to substitute into the Twig template.
 *
 * Usage example:
 * @code
 * $build['hello']  = [
 *   '#type' => 'sfc',
 *   '#component_id' => 'say_hello',
 *   '#context' => [
 *     'name' => 'Sam',
 *   ]
 * ];
 * @endcode
 *
 * @RenderElement("sfc")
 */
class ComponentElement extends RenderElement implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return [
      'preRender',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#pre_render' => [
        [$class, 'preRender'],
      ],
      '#component_id' => '',
      '#context' => [],
    ];
  }

  /**
   * Pre-renders a component.
   *
   * @param array $element
   *   The element.
   *
   * @return array
   *   The modified element.
   */
  public static function preRender(array $element) {
    $instance = \Drupal::service('plugin.manager.single_file_component')
      ->createInstance($element['#component_id']);
    $element['inline_template'] = [
      '#type' => 'inline_template',
      '#template' => '{% include "' . ComponentNameHelper::getTemplateName($instance) . '.html.twig" %}',
      '#context' => $element['#context'],
    ];
    return $element;
  }

}
