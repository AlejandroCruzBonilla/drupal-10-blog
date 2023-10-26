<?php

namespace Drupal\view_mode_switch;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityViewModeInterface;
use Drupal\view_mode_switch\Entity\EntityFieldManagerInterface;

/**
 * Provides the view mode helper service.
 */
class ViewModeHelper implements ViewModeHelperInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The view mode switch entity field manager.
   *
   * @var \Drupal\view_mode_switch\Entity\EntityFieldManagerInterface
   */
  protected $viewModeSwitchEntityFieldManager;

  /**
   * Constructs a new ViewModeHelper.
   *
   * @param \Drupal\view_mode_switch\Entity\EntityFieldManagerInterface $view_mode_switch_field_manager
   *   The view mode switch entity field manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityFieldManagerInterface $view_mode_switch_field_manager, EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->viewModeSwitchEntityFieldManager = $view_mode_switch_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getName(EntityViewModeInterface $entity): string {
    [/* Entity type */, $name] = explode('.', (string) $entity->id());

    return $name;
  }

  /**
   * {@inheritdoc}
   */
  public function preDelete(EntityViewModeInterface $entity): void {
    $view_mode = $this->getName($entity);

    // Remove view mode from field storage configurations.
    $this->viewModeSwitchEntityFieldManager->removeOriginViewModeFromFieldStorageConfigs($view_mode);
  }

}
