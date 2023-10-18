<?php

namespace Drupal\sfc;

use Drupal\Core\Render\Element\InlineTemplate;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\Template\Attribute;

/**
 * Contains trusted render callbacks to support D9.
 */
class RenderCallbacks implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return [
      'preRenderComponent',
    ];
  }

  /**
   * Adds attributes as context to the component template.
   *
   * @param array $element
   *   The element.
   *
   * @return array
   *   The modified element.
   */
  public static function preRenderComponent(array $element) {
    // Pass attributes to the template.
    $context_attributes = isset($element['#context']['attributes']) ? $element['#context']['attributes'] : [];
    $element_attributes = isset($element['#attributes']) ? $element['#attributes'] : [];
    $merged_attributes = array_merge_recursive($context_attributes, $element_attributes);
    $element['#context']['attributes'] = new Attribute($merged_attributes);
    // Pass cache to the template.
    if (isset($element['#cache'])) {
      $element['#context']['cache']['#cache'] = $element['#cache'];
    }
    return InlineTemplate::preRenderInlineTemplate($element);
  }

}
