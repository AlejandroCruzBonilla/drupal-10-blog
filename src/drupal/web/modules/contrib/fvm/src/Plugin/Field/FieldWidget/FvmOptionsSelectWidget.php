<?php

namespace Drupal\fvm\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsSelectWidget;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'fvm_options_select' widget.
 *
 * @FieldWidget(
 *   id = "fvm_options_select",
 *   label = @Translation("Fvm View Mode Select list"),
 *   field_types = {
 *     "entity_reference",
 *     "list_integer",
 *     "list_float",
 *     "list_string"
 *   },
 *   multiple_values = TRUE
 * )
 */
class FvmOptionsSelectWidget extends OptionsSelectWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'default_option' => t('Default'),
      'remove_default_option' => 0,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['default_option'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Change label of none option'),
      '#default_value' => empty($this->getSetting('default_option'))
      ? $this->t('Default') : $this->getSetting('default_option'),
    ];
    $form['remove_default_option'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Remove default option from dropdown'),
      '#default_value' => empty($this->getSetting('remove_default_option'))
      ? 0 : $this->getSetting('remove_default_option'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    if ($this->getSetting('remove_default_option')) {
      $summary[] = $this->t('Remove default option from dropdown: Yes');
      return $summary;
    }
    $summary[] = $this->t('Remove default option from dropdown: No');
    $summary[] = $this->t('Default option label: @label', ['@label' => $this->getSetting('default_option')]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEmptyLabel() {
    // Remove default option from dropdown.
    if ($this->getSetting('remove_default_option')) {
      return NULL;
    }

    if ($this->multiple) {
      // Multiple select: add a 'none' option for non-required fields.
      if (!$this->required) {
        return $this->getSetting('default_option');
      }
    }
    else {
      // Single select: add a 'none' option for non-required fields,
      // and a 'select a value' option for required fields that do not come
      // with a value selected.
      if (!$this->required) {
        return $this->getSetting('default_option');
      }
      if (!$this->has_value) {
        return t('- Select a value -');
      }
    }
  }

}
