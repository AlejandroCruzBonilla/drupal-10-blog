<?php

namespace Drupal\view_mode_switch\Entity;

use Drupal\Core\Entity\EntityFieldManagerInterface as CoreEntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemListInterface;

/**
 * Manages the discovery of view mode switch entity fields.
 */
class EntityFieldManager implements EntityFieldManagerInterface {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new EntityFieldManager.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(CoreEntityFieldManagerInterface $entity_field_manager, EntityTypeManagerInterface $entity_type_manager) {
    $this->entityFieldManager = $entity_field_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getApplicableField(FieldableEntityInterface $entity, $view_mode): ?ViewModeSwitchItemListInterface {
    /** @var array $cache */
    $cache = &drupal_static(__METHOD__, []);

    // Prepare cache ID.
    $cid = implode(':', [
      $entity->getEntityTypeId(),
      $entity->bundle(),
      $view_mode,
      $entity->id() ?: $entity->uuid(),
    ]);

    if (!isset($cache[$cid])) {
      $cache[$cid] = FALSE;

      // Process available view mode switch fields. First applicable field will
      // be used for a potential view mode switch.
      foreach ($this->getResponsibleFields($entity, $view_mode) as $field) {
        // Field is applicable for given origin view mode?
        if ($field->isApplicable($view_mode)) {
          $cache[$cid] = $field;
          break;
        }
      }
    }

    return $cache[$cid] ?: NULL;
  }

  /**
   * Gets view mode switch field candidate names for a given entity.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity holding the potential view mode switch field(s).
   *
   * @return array
   *   An array of view mode switch field candidate names.
   */
  protected function getFieldCandidateNames(FieldableEntityInterface $entity): array {
    /** @var array $cache */
    $cache = &drupal_static(__METHOD__, []);
    $entity_type_id = $entity->getEntityTypeId();
    $bundle = $entity->bundle();

    // Prepare cache ID.
    $cid = implode(':', [
      $entity_type_id,
      $bundle,
    ]);

    // Determine field candidate names (if not statically cached already).
    if (!isset($cache[$cid])) {
      $cache[$cid] = [];
      $field_map = $this->getFieldMap();

      if (!empty($field_map[$entity_type_id])) {
        $cache[$cid] = array_keys(array_filter($field_map[$entity_type_id], function (array $mapping) use ($bundle) {
          return isset($mapping['bundles'][$bundle]);
        }));
      }
    }

    return $cache[$cid];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldMap(): array {
    return $this->entityFieldManager->getFieldMapByFieldType('view_mode_switch');
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getFieldStorageDefinitionsUsingOriginViewMode($view_mode): array {
    $storages = [];

    // Process all view mode switch fields.
    foreach ($this->getFieldMap() as $entity_type => $fields) {
      foreach (array_keys($fields) as $field_name) {
        /** @var \Drupal\field\FieldStorageConfigInterface $field_storage */
        $field_storage = $this->entityTypeManager
          ->getStorage('field_storage_config')
          ->load($entity_type . '.' . $field_name);

        // Determine origin view mode setting.
        if (($origin_view_modes = $field_storage->getSetting('origin_view_modes')) && is_array($origin_view_modes)) {
          if (isset($origin_view_modes[$view_mode])) {
            $storages[$field_storage->id()] = $field_storage;
          }
        }

      }
    }

    return $storages;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getFieldStorageDefinitionsWithoutOriginViewMode(): array {
    $storages = [];

    // Process all view mode switch fields.
    foreach ($this->getFieldMap() as $entity_type => $fields) {
      foreach (array_keys($fields) as $field_name) {
        /** @var \Drupal\field\FieldStorageConfigInterface $field_storage */
        $field_storage = $this->entityTypeManager
          ->getStorage('field_storage_config')
          ->load($entity_type . '.' . $field_name);

        if (!$field_storage->getSetting('origin_view_modes')) {
          $storages[$field_storage->id()] = $field_storage;
        }
      }
    }

    return $storages;
  }

  /**
   * {@inheritdoc}
   */
  public function getResponsibleFields(FieldableEntityInterface $entity, $view_mode): array {
    /** @var array $cache */
    $cache = &drupal_static(__METHOD__, []);

    // Prepare cache ID.
    $cid = implode(':', [
      $entity->getEntityTypeId(),
      $entity->bundle(),
      $view_mode,
      $entity->id() ?: $entity->uuid(),
    ]);

    if (!isset($cache[$cid])) {
      $cache[$cid] = [];

      if (($field_candidates = $this->getFieldCandidateNames($entity))) {
        foreach ($field_candidates as $field_name) {
          /** @var \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemListInterface $field */
          $field = $entity->get($field_name);

          // Field is responsible for given origin view mode?
          if ($field->isResponsible($view_mode)) {
            $cache[$cid][$field_name] = $field;
          }
        }
      }
    }

    return $cache[$cid];
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function removeOriginViewModeFromFieldStorageConfigs($view_mode): void {
    // Process all view mode switch fields using given view mode as origin view
    // mode.
    foreach ($this->getFieldStorageDefinitionsUsingOriginViewMode($view_mode) as $field_storage) {
      // Load current origin view mode setting.
      if (($origin_view_modes = $field_storage->getSetting('origin_view_modes')) && is_array($origin_view_modes)) {
        // Remove deleted origin view mode from settings.
        unset($origin_view_modes[$view_mode]);
        $field_storage->setSetting('origin_view_modes', $origin_view_modes);
      }

      // Re-save view mode switch field storage configuration.
      $field_storage->save();
    }
  }

}
