<?php

namespace Drupal\view_mode_switch\Plugin\Field\FieldFormatter;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\EntityViewModeInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FormatterInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for 'View mode switch formatter' plugin implementations.
 */
abstract class ViewModeSwitchFormatterBase extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The entity view mode storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface
   */
  protected $entityViewModeStorage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);

    // Inject entity type manager.
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $container->get('entity_type.manager');

    /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $entity_view_mode_storage */
    $entity_view_mode_storage = $entity_type_manager->getStorage('entity_view_mode');

    $instance->setEntityViewModeStorage($entity_view_mode_storage);

    return $instance;
  }

  /**
   * Loads the view mode configuration for the given entity type and mode.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   A fieldable entity.
   * @param string $name
   *   The name of the entity view mode to load.
   *
   * @return \Drupal\Core\Entity\EntityViewModeInterface|null
   *   The entity view mode on success, otherwise NULL.
   */
  protected function loadViewMode(FieldableEntityInterface $entity, string $name): ?EntityViewModeInterface {
    $entity_view_display = $this->entityViewModeStorage
      ->load($entity->getEntityTypeId() . '.' . $name);

    return $entity_view_display instanceof EntityViewModeInterface ? $entity_view_display : NULL;
  }

  /**
   * Sets the entity view mode storage.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $storage
   *   The entity view mode storage.
   *
   * @return static
   *   The object itself for chaining.
   */
  public function setEntityViewModeStorage(ConfigEntityStorageInterface $storage): FormatterInterface {
    $this->entityViewModeStorage = $storage;

    return $this;
  }

}
