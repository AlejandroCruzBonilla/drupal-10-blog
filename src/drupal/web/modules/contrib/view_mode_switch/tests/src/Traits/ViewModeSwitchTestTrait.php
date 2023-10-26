<?php

namespace Drupal\Tests\view_mode_switch\Traits;

use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Core\Entity\EntityViewModeInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\FieldConfigInterface;

/**
 * Provides common helper methods for view mode switch tests.
 */
trait ViewModeSwitchTestTrait {

  /**
   * Field: foo.
   *
   * @var \Drupal\field\FieldConfigInterface
   */
  protected $fieldFoo;

  /**
   * Field: bar_baz.
   *
   * @var \Drupal\field\FieldConfigInterface
   */
  protected $fieldBarBaz;

  /**
   * View mode: foo.
   *
   * @var \Drupal\Core\Entity\EntityViewModeInterface
   */
  protected $viewModeFoo;

  /**
   * View mode: foo1.
   *
   * @var \Drupal\Core\Entity\EntityViewModeInterface
   */
  protected $viewModeFoo1;

  /**
   * View mode: foo2.
   *
   * @var \Drupal\Core\Entity\EntityViewModeInterface
   */
  protected $viewModeFoo2;

  /**
   * View mode: bar.
   *
   * @var \Drupal\Core\Entity\EntityViewModeInterface
   */
  protected $viewModeBar;

  /**
   * View mode: baz.
   *
   * @var \Drupal\Core\Entity\EntityViewModeInterface
   */
  protected $viewModeBaz;

  /**
   * View mode: bar_baz1.
   *
   * @var \Drupal\Core\Entity\EntityViewModeInterface
   */
  protected $viewModeBarBaz1;

  /**
   * Create view mode.
   *
   * @param string $name
   *   The view mode name.
   * @param string $entity_type_id
   *   The ID of the entity type to create the view mode for (defaults to
   *   'entity_test').
   *
   * @return \Drupal\Core\Entity\EntityViewModeInterface
   *   The view mode config entity.
   */
  protected function createViewMode($name, $entity_type_id = 'entity_test'): EntityViewModeInterface {
    $view_mode = EntityViewMode::create([
      'id' => $entity_type_id . '.' . $name,
      'targetEntityType' => $entity_type_id,
      'status' => TRUE,
      'enabled' => TRUE,
      'label' => $name . ' label',
    ]);

    $view_mode->save();

    return $view_mode;
  }

  /**
   * Create view mode switch field.
   *
   * @param string $name
   *   The name of the field. Will be prefixed with 'field_'.
   * @param array $origin_view_modes
   *   The names of the origin view modes.
   * @param array $allowed_view_modes
   *   The names of the allowed view modes.
   * @param bool $required
   *   Whether the field should be required.
   * @param string $entity_type_id
   *   The ID of the entity type to create the view mode switch field for
   *   (defaults to 'entity_test').
   * @param string $bundle
   *   The name of the bundle to create the view mode switch field for (defaults
   *   to 'entity_test').
   *
   * @return \Drupal\field\FieldConfigInterface
   *   The view mode switch field config entity.
   */
  protected function createViewModeSwitchField($name, array $origin_view_modes, array $allowed_view_modes, $required = FALSE, $entity_type_id = 'entity_test', $bundle = 'entity_test'): FieldConfigInterface {
    FieldStorageConfig::create([
      'field_name' => 'field_' . $name,
      'entity_type' => $entity_type_id,
      'type' => 'view_mode_switch',
      'settings' => [
        'origin_view_modes' => array_combine($origin_view_modes, $origin_view_modes),
      ],
    ])->save();

    $field = FieldConfig::create([
      'entity_type' => $entity_type_id,
      'field_name' => 'field_' . $name,
      'bundle' => $bundle,
      'settings' => [
        'allowed_view_modes' => array_combine($allowed_view_modes, $allowed_view_modes),
      ],
    ])->setRequired(!empty($required));

    $field->save();

    return $field;
  }

  /**
   * Returns the name of an entity view mode by its ID.
   *
   * @param string $entity_id
   *   The ID of the entity view mode to get the name for.
   *
   * @return string|null
   *   The entity view mode name on success, otherwise NULL.
   */
  protected function getViewModeNameFromId($entity_id): ?string {
    [/* Entity type */, $view_mode] = explode('.', $entity_id);

    return $view_mode ?: NULL;
  }

  /**
   * Set up default tests assets (fields and view modes).
   */
  protected function setupTestAssets(): void {
    // Create 'foo' view mode.
    $this->viewModeFoo = $this->createViewMode('foo');

    // Create 'foo1' view mode.
    $this->viewModeFoo1 = $this->createViewMode('foo1');

    // Create 'foo2' view mode.
    $this->viewModeFoo2 = $this->createViewMode('foo2');

    // Create 'bar' view mode.
    $this->viewModeBar = $this->createViewMode('bar');

    // Create 'baz' view mode.
    $this->viewModeBaz = $this->createViewMode('baz');

    // Create 'bar_baz1' view mode.
    $this->viewModeBarBaz1 = $this->createViewMode('bar_baz1');

    // Create a required view mode switch field with several allowed view modes.
    $field_foo_origin_view_modes = ['foo'];
    $field_foo_allowed_view_modes = ['foo1', 'foo2'];
    $this->fieldFoo = $this->createViewModeSwitchField('foo', $field_foo_origin_view_modes, $field_foo_allowed_view_modes, TRUE);

    // Create an optional view mode switch field for multiple origins.
    $field_bar_baz_origin_view_modes = ['bar', 'baz'];
    $field_bar_baz_allowed_view_modes = ['bar_baz1'];
    $this->fieldBarBaz = $this->createViewModeSwitchField('bar_baz', $field_bar_baz_origin_view_modes, $field_bar_baz_allowed_view_modes);
  }

}
