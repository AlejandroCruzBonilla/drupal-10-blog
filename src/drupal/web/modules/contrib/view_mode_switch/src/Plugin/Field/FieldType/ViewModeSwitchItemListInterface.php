<?php

namespace Drupal\view_mode_switch\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Interface for view mode switch lists of field items.
 *
 * @property string|null $value
 */
interface ViewModeSwitchItemListInterface extends FieldItemListInterface {

  /**
   * Returns the view modes allowed to switch to.
   *
   * @return array
   *   A keyed array of view mode names for all configured allowed view modes.
   *   Both the key and the value are the view mode name.
   *
   * @see \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemInterface::getAllowedViewModes()
   */
  public function getAllowedViewModes(): array;

  /**
   * Returns the view mode to switch to.
   *
   * @return string|null
   *   The view mode name on success, otherwise NULL.
   *
   * @see \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemInterface::getViewMode()
   */
  public function getViewMode(): ?string;

  /**
   * Returns the view modes to switch.
   *
   * @return array
   *   A keyed array of view mode names for all configured origin view modes.
   *   Both the key and the value are the view mode name.
   *
   * @see \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemInterface::getOriginViewModes()
   */
  public function getOriginViewModes(): array;

  /**
   * Returns whether the field is applicable for the given origin view mode.
   *
   * @param string $view_mode
   *   A view mode name.
   *
   * @return bool
   *   Whether the field is applicable for the given origin view mode.
   *
   * @see \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemInterface::isApplicable()
   */
  public function isApplicable(string $view_mode): bool;

  /**
   * Returns whether the field is responsible for the given origin view mode.
   *
   * @param string $view_mode
   *   A view mode name.
   *
   * @return bool
   *   Whether the field is responsible for the given origin view mode.
   *
   * @see \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemInterface::isResponsible()
   */
  public function isResponsible(string $view_mode): bool;

}
