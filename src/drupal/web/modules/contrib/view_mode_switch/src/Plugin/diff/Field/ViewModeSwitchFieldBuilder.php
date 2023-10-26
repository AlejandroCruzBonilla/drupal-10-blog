<?php

namespace Drupal\view_mode_switch\Plugin\diff\Field;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\diff\FieldDiffBuilderBase;
use Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemListInterface;

/**
 * Plugin to compare view mode switch fields.
 *
 * @FieldDiffBuilder(
 *   id = "view_mode_switch_field_diff_builder",
 *   label = @Translation("View Mode Switch Field Diff"),
 *   field_types = {
 *     "view_mode_switch"
 *   },
 * )
 */
class ViewModeSwitchFieldBuilder extends FieldDiffBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function build(FieldItemListInterface $field_items): array {
    $result = [];

    if ($field_items instanceof ViewModeSwitchItemListInterface) {
      /** @var \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemInterface $item */
      foreach ($field_items as $delta => $item) {
        $values = $item->getValue();

        if (is_array($values) && !empty($values['value'])) {
          $result[$delta][] = $values['value'];
        }
      }
    }

    return $result;
  }

}
