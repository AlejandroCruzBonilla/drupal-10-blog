<?php

namespace Drupal\view_mode_switch;

use Drupal\Core\Entity\EntityViewModeInterface;

/**
 * Interface for view mode helper service classes.
 */
interface ViewModeHelperInterface {

  /**
   * Returns the name of an entity view mode.
   *
   * @param \Drupal\Core\Entity\EntityViewModeInterface $entity
   *   The entity view mode object.
   *
   * @return string
   *   The entity view mode name on success, otherwise NULL.
   */
  public function getName(EntityViewModeInterface $entity): string;

  /**
   * Act before entity view mode deletion.
   *
   * This method ensures view mode switch field storage configuration changes
   * needed when an entity view mode is deleted that is used as an origin view
   * mode.
   *
   * @param \Drupal\Core\Entity\EntityViewModeInterface $entity
   *   The entity object for the entity view mode that is about to be deleted.
   */
  public function preDelete(EntityViewModeInterface $entity): void;

}
