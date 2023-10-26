<?php

namespace Drupal\view_mode_switch\Entity;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemListInterface;

/**
 * Provides an interface for a view mode switch entity field manager services.
 */
interface EntityFieldManagerInterface {

  /**
   * Gets an applicable view mode switch field for a given origin view mode.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity holding the potential view mode switch field(s).
   * @param string $view_mode
   *   The name of the origin view mode to switch.
   *
   * @return \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemListInterface|null
   *   A view mode switch field object on success, otherwise NULL.
   */
  public function getApplicableField(FieldableEntityInterface $entity, string $view_mode): ?ViewModeSwitchItemListInterface;

  /**
   * Gets a lightweight map of view mode switch fields across bundles.
   *
   * @return array
   *   An array keyed by entity type. Each value is an array which keys are
   *   field names and value is an array with two entries:
   *   - type: The field type.
   *   - bundles: An associative array of the bundles in which the field
   *     appears, where the keys and values are both the bundle's machine name.
   */
  public function getFieldMap(): array;

  /**
   * Gets all view mode switch field storages using given origin view mode.
   *
   * @param string $view_mode
   *   The view mode name to get the view mode switch field storages for.
   *
   * @return \Drupal\field\FieldStorageConfigInterface[]
   *   The view mode switch field storages using the given origin view mode.
   */
  public function getFieldStorageDefinitionsUsingOriginViewMode(string $view_mode): array;

  /**
   * Gets all view mode switch field storages without origin view mode.
   *
   * @return \Drupal\field\FieldStorageConfigInterface[]
   *   The view mode switch field storages having no origin view modes
   *   configured.
   */
  public function getFieldStorageDefinitionsWithoutOriginViewMode(): array;

  /**
   * Gets all responsible view mode switch fields for a given origin view mode.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity holding the potential view mode switch field(s).
   * @param string $view_mode
   *   The name of the origin view mode to switch.
   *
   * @return \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemListInterface[]
   *   A keyed array of responsible view mode switch fields. The key is the
   *   field name, the value is the corresponding view mode switch entity field
   *   object.
   */
  public function getResponsibleFields(FieldableEntityInterface $entity, string $view_mode): array;

  /**
   * Remove given origin view mode from view mode switch field storage configs.
   *
   * @param string $view_mode
   *   The name of the origin view mode to remove.
   */
  public function removeOriginViewModeFromFieldStorageConfigs(string $view_mode): void;

}
