<?php

namespace Drupal\sfc;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an interface for an embeddable component form.
 *
 * Components should implement this interface if they can be configured through
 * a user interface. Typically this is used when a plugin is derived, like a
 * block.
 */
interface ComponentFormInterface {

  /**
   * Form build handler.
   *
   * All values in the form will likely be saved and passed to your template
   * as-is as context, so make sure to validate and sanitize them as if they
   * were untrusted user input.
   *
   * @param array $form
   *   An associative array containing the initial structure of the plugin form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $default_values
   *   An array of default values for the form.
   *   The component has no awareness about the storage of the caller, which is
   *   why this is required.
   *
   * @return array
   *   The form structure.
   */
  public function buildContextForm(array $form, FormStateInterface $form_state, array $default_values = []);

  /**
   * Form validation handler.
   *
   * @param array $form
   *   An associative array containing the structure of the plugin form as built
   *   by static::buildConfigurationForm().
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateContextForm(array &$form, FormStateInterface $form_state);

  /**
   * Form submission handler.
   *
   * This should generally only be used to alter $form_state values before they
   * are used by the caller.
   *
   * @param array $form
   *   An associative array containing the structure of the plugin form as built
   *   by static::buildConfigurationForm().
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitContextForm(array &$form, FormStateInterface $form_state);

}
