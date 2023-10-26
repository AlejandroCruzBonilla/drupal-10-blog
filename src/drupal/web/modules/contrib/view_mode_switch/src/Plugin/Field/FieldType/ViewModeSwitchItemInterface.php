<?php

namespace Drupal\view_mode_switch\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TypedData\OptionsProviderInterface;

/**
 * Defines an interface for the view mode switch field item.
 *
 * @property string|null $value
 */
interface ViewModeSwitchItemInterface extends FieldItemInterface, OptionsProviderInterface {

  /**
   * Returns the view modes allowed to switch to.
   *
   * @return array
   *   A keyed array of view mode names for all configured allowed view modes.
   *   Both the key and the value are the view mode name.
   *
   * @see \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemListInterface::getAllowedViewModes()
   */
  public function getAllowedViewModes(): array;

  /**
   * Returns the view modes to switch.
   *
   * @return array
   *   A keyed array of view mode names for all configured origin view modes.
   *   Both the key and the value are the view mode name.
   *
   * @see \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemListInterface::getOriginViewModes()
   */
  public function getOriginViewModes(): array;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   (optional) The user account for which to filter the settable options. If
   *   omitted, all settable options are returned.
   * @param bool $allowed_only
   *   Whether to only return allowed values.
   *
   * @return array
   *   An array of settable options for the object that may be used in an
   *   Options widget, usually when new data should be entered. It may either be
   *   a flat array of option labels keyed by values, or a two-dimensional array
   *   of option groups (array of flat option arrays, keyed by option group
   *   label). Note that labels should NOT be sanitized.
   */
  public function getSettableOptions(AccountInterface $account = NULL, bool $allowed_only = TRUE): array;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   (optional) The user account for which to filter the settable options. If
   *   omitted, all settable options are returned.
   * @param bool $allowed_only
   *   Whether to only return allowed values.
   *
   * @return array
   *   An array of settable values.
   */
  public function getSettableValues(AccountInterface $account = NULL, bool $allowed_only = TRUE): array;

  /**
   * Returns the view mode to switch to.
   *
   * @return string|null
   *   The name of the view mode to switch to on success, otherwise NULL.
   *
   * @see \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemListInterface::getViewMode()
   */
  public function getViewMode(): ?string;

  /**
   * Returns whether the field is applicable for the given origin view mode.
   *
   * @param string $view_mode
   *   A view mode name.
   *
   * @return bool
   *   Whether the field is applicable for the given origin view mode.
   *
   * @see \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemListInterface::isApplicable()
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
   * @see \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemListInterface::isResponsible()
   */
  public function isResponsible(string $view_mode): bool;

}
