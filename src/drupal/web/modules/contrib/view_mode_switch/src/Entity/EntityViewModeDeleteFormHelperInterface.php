<?php

namespace Drupal\view_mode_switch\Entity;

use Drupal\Core\Form\FormStateInterface;

/**
 * Interface for entity display mode delete form helper service classes.
 */
interface EntityViewModeDeleteFormHelperInterface {

  /**
   * Alter entity view mode delete confirm form.
   *
   * This method adds potential custom view mode switch field storage
   * configuration changes triggered when an entity view mode is deleted that is
   * used as an origin view mode.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @see \Drupal\Core\Config\Entity\ConfigDependencyDeleteFormTrait::addDependencyListsToForm()
   */
  public function alter(array &$form, FormStateInterface $form_state): void;

}
