<?php

namespace Drupal\view_mode_switch\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldFilteredMarkup;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'view_mode_switch_default' formatter.
 *
 * @FieldFormatter(
 *   id = "view_mode_switch_default",
 *   label = @Translation("Default"),
 *   field_types = {
 *     "view_mode_switch"
 *   }
 * )
 */
class ViewModeSwitchDefaultFormatter extends ViewModeSwitchFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    $elements = [];
    $entity = $items->getEntity();

    /** @var \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemInterface $item */
    foreach ($items as $delta => $item) {
      $value = $item->value ?? '';

      // View mode found -> display its human-readable label.
      if ($value === $item->getViewMode() && ($view_mode = $this->loadViewMode($entity, $value))) {
        $elements[$delta] = [
          '#markup' => $view_mode->label(),
          '#allowed_tags' => FieldFilteredMarkup::allowedTags(),
        ];
      }
    }

    return $elements;
  }

}
