<?php

namespace Drupal\view_mode_switch\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldFilteredMarkup;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'view_mode_switch_machine_name' formatter.
 *
 * @FieldFormatter(
 *   id = "view_mode_switch_machine_name",
 *   label = @Translation("Machine name"),
 *   field_types = {
 *     "view_mode_switch"
 *   }
 * )
 */
class ViewModeSwitchMachineNameFormatter extends ViewModeSwitchFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    $elements = [];

    /** @var \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemInterface $item */
    foreach ($items as $delta => $item) {
      $value = $item->value;

      // View mode found -> display its machine name.
      if ($value === $item->getViewMode()) {
        $elements[$delta] = [
          '#markup' => $value,
          '#allowed_tags' => FieldFilteredMarkup::allowedTags(),
        ];
      }
    }

    return $elements;
  }

}
