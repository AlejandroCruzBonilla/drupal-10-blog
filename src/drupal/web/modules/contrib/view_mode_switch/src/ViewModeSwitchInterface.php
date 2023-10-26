<?php

namespace Drupal\view_mode_switch;

use Drupal\Core\Entity\FieldableEntityInterface;

/**
 * Interface for view mode switch service classes.
 */
interface ViewModeSwitchInterface {

  /**
   * Gets the view mode to switch to (if applicable).
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity to process for view mode switches.
   * @param string $view_mode
   *   The origin view mode.
   *
   * @return string|null
   *   The name of the view mode to switch to.
   */
  public function getViewModeToSwitchTo(FieldableEntityInterface $entity, string $view_mode): ?string;

}
