<?php

namespace Drupal\Tests\view_mode_switch\Kernel\Entity;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\FieldStorageConfigInterface;
use Drupal\Tests\view_mode_switch\Kernel\ViewModeSwitchTestBase;
use Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemListInterface;

/**
 * Tests the view mode switch entity field manager.
 *
 * @group view_mode_switch
 * @coversDefaultClass \Drupal\view_mode_switch\Entity\EntityFieldManager
 */
class EntityFieldManagerTest extends ViewModeSwitchTestBase {

  /**
   * The view mode switch entity field manager.
   *
   * @var \Drupal\view_mode_switch\Entity\EntityFieldManagerInterface
   */
  protected $viewModeSwitchEntityFieldManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->viewModeSwitchEntityFieldManager = $this->container->get('view_mode_switch.entity_field_manager');
  }

  /**
   * Tests applicable view mode switch field for a given origin view mode.
   *
   * @covers ::getApplicableField
   */
  public function testGetApplicableField(): void {
    $this->assertIsString($this->viewModeFoo->id());
    $view_mode_foo = $this->getViewModeNameFromId($this->viewModeFoo->id());
    $this->assertIsString($view_mode_foo);

    $this->assertIsString($this->viewModeFoo1->id());
    $view_mode_foo1 = $this->getViewModeNameFromId($this->viewModeFoo1->id());
    $this->assertIsString($view_mode_foo1);

    $this->assertIsString($this->viewModeBar->id());
    $view_mode_bar = $this->getViewModeNameFromId($this->viewModeBar->id());
    $this->assertIsString($view_mode_bar);

    $this->assertIsString($this->viewModeBaz->id());
    $view_mode_baz = $this->getViewModeNameFromId($this->viewModeBaz->id());
    $this->assertIsString($view_mode_baz);

    $this->assertIsString($this->viewModeBarBaz1->id());
    $view_mode_bar_baz1 = $this->getViewModeNameFromId($this->viewModeBarBaz1->id());
    $this->assertIsString($view_mode_bar_baz1);

    // Test entity without view mode switch values.
    $entity = EntityTest::create(['name' => $this->randomString()]);
    $this->assertNull($this->viewModeSwitchEntityFieldManager->getApplicableField($entity, $view_mode_foo));
    $this->assertNull($this->viewModeSwitchEntityFieldManager->getApplicableField($entity, $view_mode_bar));
    $this->assertNull($this->viewModeSwitchEntityFieldManager->getApplicableField($entity, $view_mode_baz));

    // Test applicable field for 'foo' view mode.
    $entity = EntityTest::create([
      'name' => $this->randomString(),
      $this->fieldFoo->getName() => $view_mode_foo1,
      $this->fieldBarBaz->getName() => $view_mode_bar_baz1,
    ]);
    $field_for_foo = $this->viewModeSwitchEntityFieldManager->getApplicableField($entity, $view_mode_foo);
    $this->assertInstanceOf(ViewModeSwitchItemListInterface::class, $field_for_foo);
    $this->assertEquals($this->fieldFoo->getName(), $field_for_foo->getName());

    // Test applicable fields for 'bar' and 'baz' view modes (should be the same
    // for both view modes).
    $entity = EntityTest::create([
      'name' => $this->randomString(),
      $this->fieldFoo->getName() => $view_mode_foo1,
      $this->fieldBarBaz->getName() => $view_mode_bar_baz1,
    ]);
    $field_for_bar = $this->viewModeSwitchEntityFieldManager->getApplicableField($entity, $view_mode_bar);
    $field_for_baz = $this->viewModeSwitchEntityFieldManager->getApplicableField($entity, $view_mode_baz);
    $this->assertInstanceOf(ViewModeSwitchItemListInterface::class, $field_for_bar);
    $this->assertInstanceOf(ViewModeSwitchItemListInterface::class, $field_for_baz);
    $this->assertEquals($field_for_bar->getName(), $field_for_baz->getName());
    $this->assertEquals($this->fieldBarBaz->getName(), $field_for_bar->getName());
    $this->assertEquals($this->fieldBarBaz->getName(), $field_for_baz->getName());
  }

  /**
   * Tests the lightweight map of view mode switch fields across bundles.
   *
   * @covers ::getFieldMap
   */
  public function testGetFieldMap(): void {
    $field_map = $this->viewModeSwitchEntityFieldManager->getFieldMap();

    $expected = [
      'entity_test' => [
        'field_foo' => [
          'type' => 'view_mode_switch',
          'bundles' => [
            'entity_test' => 'entity_test',
          ],
        ],
        'field_bar_baz' => [
          'type' => 'view_mode_switch',
          'bundles' => [
            'entity_test' => 'entity_test',
          ],
        ],
      ],
    ];

    $this->assertSame($expected, $field_map);
  }

  /**
   * Tests view mode switch field storages using given origin view mode.
   *
   * @covers ::getFieldStorageDefinitionsUsingOriginViewMode
   */
  public function testGetFieldStorageDefinitionsUsingOriginViewMode(): void {
    // Test field storages using 'foo' origin view mode.
    $field_storages_foo = $this->viewModeSwitchEntityFieldManager->getFieldStorageDefinitionsUsingOriginViewMode('foo');
    /** @var \Drupal\field\FieldStorageConfigInterface $field_storage_foo */
    $field_storage_foo = $this->fieldFoo->getFieldStorageDefinition();
    $this->assertCount(1, $field_storages_foo);
    $this->assertIsString($field_storage_foo->id());
    $this->assertArrayHasKey($field_storage_foo->id(), $field_storages_foo);
    $this->assertInstanceOf(FieldStorageConfigInterface::class, $field_storages_foo[$field_storage_foo->id()]);

    // Test field storages using 'foo1' origin view mode (should be none).
    $field_storages_foo1 = $this->viewModeSwitchEntityFieldManager->getFieldStorageDefinitionsUsingOriginViewMode('foo1');
    $this->assertCount(0, $field_storages_foo1);

    // Test field storages using 'bar' or 'baz' origin view modes.
    $field_storages_bar = $this->viewModeSwitchEntityFieldManager->getFieldStorageDefinitionsUsingOriginViewMode('bar');
    $field_storages_baz = $this->viewModeSwitchEntityFieldManager->getFieldStorageDefinitionsUsingOriginViewMode('baz');
    $field_storage_bar_baz = $this->fieldBarBaz->getFieldStorageDefinition();
    $this->assertInstanceOf(FieldStorageConfigInterface::class, $field_storage_bar_baz);
    $this->assertSame(array_keys($field_storages_bar), array_keys($field_storages_baz));
    $this->assertCount(1, $field_storages_bar);
    $this->assertCount(1, $field_storages_baz);
    $this->assertIsString($field_storage_bar_baz->id());
    $this->assertArrayHasKey($field_storage_bar_baz->id(), $field_storages_bar);
    $this->assertArrayHasKey($field_storage_bar_baz->id(), $field_storages_baz);
    $this->assertInstanceOf(FieldStorageConfigInterface::class, $field_storages_bar[$field_storage_bar_baz->id()]);
    $this->assertInstanceOf(FieldStorageConfigInterface::class, $field_storages_baz[$field_storage_bar_baz->id()]);
  }

  /**
   * Tests view mode switch field storages without origin view mode.
   *
   * @covers ::getFieldStorageDefinitionsWithoutOriginViewMode
   */
  public function testGetFieldStorageDefinitionsWithoutOriginViewMode(): void {
    // Test that no view mode switch fields without origin view mode are
    // available initially.
    $field_storages = $this->viewModeSwitchEntityFieldManager->getFieldStorageDefinitionsWithoutOriginViewMode();
    $this->assertCount(0, $field_storages);

    // Test view mode switch field without origin view mode is returned
    // correctly.
    $field_no_origin = $this->createViewModeSwitchField('no_origin', [], []);
    /** @var \Drupal\field\FieldStorageConfigInterface $field_storage_no_origin */
    $field_storage_no_origin = $field_no_origin->getFieldStorageDefinition();
    $field_storages = $this->viewModeSwitchEntityFieldManager->getFieldStorageDefinitionsWithoutOriginViewMode();
    $this->assertCount(1, $field_storages);
    $this->assertIsString($field_storage_no_origin->id());
    $this->assertArrayHasKey($field_storage_no_origin->id(), $field_storages);
    $this->assertInstanceOf(FieldStorageConfigInterface::class, $field_storages[$field_storage_no_origin->id()]);
    // Add origin view mode to view ,ode switch field storage without origin
    // view mode and test again.
    $this->assertIsString($this->viewModeFoo2->id());
    $view_mode_foo2 = $this->getViewModeNameFromId($this->viewModeFoo2->id());
    $field_storage_no_origin->setSetting('origin_view_modes', [$view_mode_foo2 => $view_mode_foo2]);
    $field_storage_no_origin->save();
    $field_storages = $this->viewModeSwitchEntityFieldManager->getFieldStorageDefinitionsWithoutOriginViewMode();
    $this->assertCount(0, $field_storages);
  }

  /**
   * Tests responsible view mode switch fields for a given origin view mode.
   *
   * @covers ::getResponsibleFields
   */
  public function testGetResponsibleFields(): void {
    // Add another field using 'baz' origin view mode.
    $field_another_baz = $this->createViewModeSwitchField('another_baz', ['baz'], ['bar_baz1']);

    // Create test entity.
    $entity = EntityTest::create([
      'name' => $this->randomString(),
    ]);

    // Test view mode switch fields responsible for 'foo' origin view mode.
    $fields_foo = $this->viewModeSwitchEntityFieldManager->getResponsibleFields($entity, 'foo');
    $this->assertCount(1, $fields_foo);
    $this->assertArrayHasKey($this->fieldFoo->getName(), $fields_foo);
    $this->assertInstanceOf(ViewModeSwitchItemListInterface::class, $fields_foo[$this->fieldFoo->getName()]);

    // Test view mode switch fields responsible for 'foo1' origin view mode
    // (should be none).
    $fields_foo1 = $this->viewModeSwitchEntityFieldManager->getResponsibleFields($entity, 'foo1');
    $this->assertCount(0, $fields_foo1);

    // Test view mode switch fields responsible for 'bar' origin view mode.
    $fields_bar = $this->viewModeSwitchEntityFieldManager->getResponsibleFields($entity, 'bar');
    $this->assertCount(1, $fields_bar);
    $this->assertArrayHasKey($this->fieldBarBaz->getName(), $fields_bar);
    $this->assertInstanceOf(ViewModeSwitchItemListInterface::class, $fields_bar[$this->fieldBarBaz->getName()]);

    // Test view mode switch fields responsible for 'baz' origin view mode.
    $fields_baz = $this->viewModeSwitchEntityFieldManager->getResponsibleFields($entity, 'baz');
    $this->assertCount(2, $fields_baz);
    $this->assertArrayHasKey($this->fieldBarBaz->getName(), $fields_baz);
    $this->assertArrayHasKey($field_another_baz->getName(), $fields_baz);
    $this->assertInstanceOf(ViewModeSwitchItemListInterface::class, $fields_baz[$this->fieldBarBaz->getName()]);
    $this->assertInstanceOf(ViewModeSwitchItemListInterface::class, $fields_baz[$field_another_baz->getName()]);
  }

  /**
   * Tests given origin view mode removal from view mode switch storage configs.
   *
   * @covers ::removeOriginViewModeFromFieldStorageConfigs
   */
  public function testRemoveOriginViewModeFromFieldStorageConfigs(): void {
    $this->assertIsString($this->viewModeBar->id());
    $view_mode_bar = $this->getViewModeNameFromId($this->viewModeBar->id());
    $this->assertNotNull($view_mode_bar);

    $this->assertIsString($this->viewModeBaz->id());
    $view_mode_baz = $this->getViewModeNameFromId($this->viewModeBaz->id());
    $this->assertNotNull($view_mode_baz);

    $field_storage_bar_baz = $this->fieldBarBaz->getFieldStorageDefinition();
    $this->assertInstanceOf(FieldStorageConfigInterface::class, $field_storage_bar_baz);

    // Test inital origin view modes of 'bar_baz' field.
    $origin_view_modes = $field_storage_bar_baz->getSetting('origin_view_modes');
    $this->assertIsArray($origin_view_modes);
    $this->assertContains($view_mode_bar, $origin_view_modes);
    $this->assertContains($view_mode_baz, $origin_view_modes);

    // Test 'bar' origin view mode removal.
    $this->viewModeSwitchEntityFieldManager->removeOriginViewModeFromFieldStorageConfigs($view_mode_bar);
    $field_storage_bar_baz = FieldStorageConfig::load($field_storage_bar_baz->id());
    $this->assertInstanceOf(FieldStorageConfigInterface::class, $field_storage_bar_baz);
    $origin_view_modes = $field_storage_bar_baz->getSetting('origin_view_modes');
    $this->assertIsArray($origin_view_modes);
    $this->assertNotContains($view_mode_bar, $origin_view_modes);
    $this->assertContains($view_mode_baz, $origin_view_modes);

    // Test 'baz' origin view mode removal.
    $this->viewModeSwitchEntityFieldManager->removeOriginViewModeFromFieldStorageConfigs($view_mode_baz);
    $field_storage_bar_baz = FieldStorageConfig::load($field_storage_bar_baz->id());
    $this->assertInstanceOf(FieldStorageConfigInterface::class, $field_storage_bar_baz);
    $origin_view_modes = $field_storage_bar_baz->getSetting('origin_view_modes');
    $this->assertIsArray($origin_view_modes);
    $this->assertNotContains($view_mode_bar, $origin_view_modes);
    $this->assertNotContains($view_mode_baz, $origin_view_modes);
    $this->assertCount(0, $origin_view_modes);
  }

}
