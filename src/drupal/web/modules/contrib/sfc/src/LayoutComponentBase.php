<?php

namespace Drupal\sfc;

use Drupal\Core\Template\Attribute;

/**
 * A base class for layout components, which have specific attribute needs.
 */
class LayoutComponentBase extends ComponentBase {

  /**
   * {@inheritdoc}
   */
  public function prepareContext(array &$context) {
    if (!isset($context['attributes'])) {
      $context['attributes'] = new Attribute();
    }
    elseif (is_array($context['attributes'])) {
      $context['attributes'] = new Attribute($context['attributes']);
    }
    if (!empty($this->pluginDefinition['layout']['regions'])) {
      foreach (array_keys($this->pluginDefinition['layout']['regions']) as $region) {
        if (!isset($context['content'][$region])) {
          $context['content'][$region] = [];
        }
        if (!isset($context['region_attributes'][$region])) {
          $context['region_attributes'][$region] = new Attribute();
        }
        elseif (is_array($context['region_attributes'][$region])) {
          $context['region_attributes'][$region] = new Attribute($context['region_attributes'][$region]);
        }
      }
    }
  }

}
