<?php

namespace Drupal\Tests\view_mode_switch\Kernel;

use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\FieldStorageConfigInterface;

/**
 * Tests the view mode helper for the view mode switch field type.
 *
 * @group view_mode_switch
 * @coversDefaultClass \Drupal\view_mode_switch\ViewModeHelper
 */
class ViewModeHelperTest extends ViewModeSwitchTestBase {

  /**
   * The view mode helper.
   *
   * @var \Drupal\view_mode_switch\ViewModeHelperInterface
   */
  protected $viewModeHelper;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->viewModeHelper = $this->container->get('view_mode_switch.view_mode_helper');
  }

  /**
   * Tests getting the view mode name based on its ID.
   *
   * @covers ::getName
   */
  public function testGetName(): void {
    $expected_name = 'test_get_name';

    /** @var \Drupal\Core\Entity\EntityViewModeInterface $entity */
    $entity = EntityViewMode::create([
      'id' => 'entity_test.' . $expected_name,
    ]);

    $this->assertEquals($expected_name, $this->viewModeHelper->getName($entity));
  }

  /**
   * Tests origin view mode deletion changing view mode switch field storages.
   *
   * @covers ::preDelete
   */
  public function testPreDelete(): void {
    $this->assertIsString($this->viewModeBar->id());
    $view_mode_bar = $this->getViewModeNameFromId($this->viewModeBar->id());
    $this->assertIsString($this->viewModeBaz->id());
    $view_mode_baz = $this->getViewModeNameFromId($this->viewModeBaz->id());

    // Test initial origin view modes of 'bar_baz' field.
    /** @var \Drupal\field\FieldStorageConfigInterface $field_storage_bar_baz */
    $field_storage_bar_baz = $this->fieldBarBaz->getFieldStorageDefinition();
    $origin_view_modes = $field_storage_bar_baz->getSetting('origin_view_modes');
    $this->assertIsArray($origin_view_modes);
    $this->assertContains($view_mode_bar, $origin_view_modes);
    $this->assertContains($view_mode_baz, $origin_view_modes);

    // Test origin view mode removal behavior on 'bar_baz' field when 'bar'
    // origin view mode is deleted (field storage configuration should be
    // updated by removing configured view mode as well).
    $this->viewModeBar->delete();
    $field_storage_bar_baz = FieldStorageConfig::load($field_storage_bar_baz->id());
    $this->assertInstanceOf(FieldStorageConfigInterface::class, $field_storage_bar_baz);
    $origin_view_modes = $field_storage_bar_baz->getSetting('origin_view_modes');
    $this->assertIsArray($origin_view_modes);
    $this->assertNotContains($view_mode_bar, $origin_view_modes);
    $this->assertContains($view_mode_baz, $origin_view_modes);

    // Test origin view mode removal behavior on 'bar_baz' field when 'baz'
    // origin view mode is deleted (field storage configuration should be
    // updated by removing configured view mode as well).
    $this->viewModeBaz->delete();
    $field_storage_bar_baz = FieldStorageConfig::load($field_storage_bar_baz->id());
    $this->assertInstanceOf(FieldStorageConfigInterface::class, $field_storage_bar_baz);
    $origin_view_modes = $field_storage_bar_baz->getSetting('origin_view_modes');
    $this->assertIsArray($origin_view_modes);
    $this->assertNotContains($view_mode_bar, $origin_view_modes);
    $this->assertNotContains($view_mode_baz, $origin_view_modes);
    $this->assertCount(0, $origin_view_modes);
  }

}
