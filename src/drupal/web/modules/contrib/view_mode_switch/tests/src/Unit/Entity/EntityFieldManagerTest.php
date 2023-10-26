<?php

namespace Drupal\Tests\view_mode_switch\Unit\Entity;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\EntityFieldManager as CoreEntityFieldManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\FieldStorageConfigInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\view_mode_switch\Entity\EntityFieldManager;
use Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemList;
use Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemListInterface;

/**
 * Tests the view mode switch entity field manager.
 *
 * @group view_mode_switch
 * @coversDefaultClass \Drupal\view_mode_switch\Entity\EntityFieldManager
 */
class EntityFieldManagerTest extends UnitTestCase {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityFieldManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * The view mode switch entity field manager.
   *
   * @var \Drupal\view_mode_switch\Entity\EntityFieldManagerInterface|\Drupal\Tests\view_mode_switch\Unit\Entity\EntityFieldManager_Test|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $viewModeSwitchEntityFieldManager;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Prepare core's entity field manager mock.
    $this->entityFieldManager = $this->getMockBuilder(CoreEntityFieldManager::class)
      ->onlyMethods([
        'getFieldMap',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $this->entityFieldManager
      ->expects($this->any())
      ->method('getFieldMap')
      ->willReturn([
        'entity_type_foo' => [
          'field_foo_vms' => [
            'type' => 'view_mode_switch',
            'bundles' => [
              'bundle_foo_foo' => 'bundle_foo_foo',
              'bundle_foo_bar' => 'bundle_foo_bar',
            ],
          ],
          'field_foo_text' => [
            'type' => 'text',
            'bundles' => [
              'bundle_foo_foo' => 'bundle_foo_foo',
              'bundle_foo_bar' => 'bundle_foo_bar',
            ],
          ],
        ],
        'entity_type_bar' => [
          'field_bar_vms1' => [
            'type' => 'view_mode_switch',
            'bundles' => [
              'bundle_bar_bar' => 'bundle_bar_bar',
            ],
          ],
          'field_bar_vms2' => [
            'type' => 'view_mode_switch',
            'bundles' => [
              'bundle_bar_bar' => 'bundle_bar_bar',
              'bundle_bar_foobar' => 'bundle_bar_foobar',
            ],
          ],
        ],
        'entity_type_foobar' => [
          'field_foobar_text' => [
            'type' => 'text',
            'bundles' => [
              'bundle_foobar_foobar' => 'bundle_foobar_foobar',
            ],
          ],
        ],
      ]);

    // Prepare entity type manager mock.
    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);

    // Prepare entity field manager mock for view mode switch fields.
    $this->viewModeSwitchEntityFieldManager = $this->getMockBuilder(EntityFieldManager_Test::class)
      ->onlyMethods([])
      ->setConstructorArgs([
        $this->entityFieldManager,
        $this->entityTypeManager,
      ])
      ->getMock();
  }

  /**
   * Data provider for getting applicable view mode switch field for view mode.
   *
   * @return array
   *   The test data.
   */
  public function dataProviderGetApplicableField(): array {
    return [
      'with-id-all-applicable' => [
        1,
        [
          'field_bar_vms1' => TRUE,
          'field_bar_vms2' => TRUE,
        ],
        'field_bar_vms1',
      ],
      'with-id-second-applicable' => [
        2,
        [
          'field_bar_vms2' => TRUE,
        ],
        'field_bar_vms2',
      ],
      'with-id-none-applicable' => [
        3,
        [],
        NULL,
      ],
      'without-id-all-applicable' => [
        NULL,
        [
          'field_bar_vms1' => TRUE,
          'field_bar_vms2' => TRUE,
        ],
        'field_bar_vms1',
      ],
    ];
  }

  /**
   * Tests getting an applicable view mode switch field for given view mode.
   *
   * @param int|null $entity_id
   *   An optional entity ID.
   * @param array $applicable
   *   Test data which field is applicable.
   * @param string|null $expected
   *   The expected view mode switch field name.
   *
   * @covers ::getApplicableField
   *
   * @dataProvider dataProviderGetApplicableField
   */
  public function testGetApplicableField(?int $entity_id, array $applicable, ?string $expected): void {
    // Prepare fieldable test entity.
    $entity = $this->createMock(FieldableEntityInterface::class);

    $entity->expects($this->atLeastOnce())
      ->method('getEntityTypeId')
      ->willReturn('entity_type_bar');

    $entity->expects($this->atLeastOnce())
      ->method('bundle')
      ->willReturn('bundle_bar_bar');

    $entity->expects($this->atLeastOnce())
      ->method('id')
      ->willReturn($entity_id);

    $entity->expects($this->atLeastOnce())
      ->method('get')
      ->willReturnCallback(function ($field_name) use ($applicable) {
        // Prepare test field item list mock.
        $field = $this->getMockBuilder(ViewModeSwitchItemList::class)
          ->onlyMethods([
            'getName',
            'isApplicable',
            'isResponsible',
          ])
          ->disableOriginalConstructor()
          ->getMock();

        $field->expects($this->any())
          ->method('getName')
          ->willReturn($field_name);

        $field->expects($this->any())
          ->method('isResponsible')
          ->willReturn(TRUE);

        $field->expects($this->any())
          ->method('isApplicable')
          ->willReturnCallback(function () use ($field_name, $applicable) {
            return !empty($applicable[$field_name]);
          });

        return $field;
      });

    $field = $this->viewModeSwitchEntityFieldManager->getApplicableField($entity, 'foo_applicable');

    if (empty($applicable)) {
      $this->assertNull($field);
    }
    else {
      $this->assertInstanceOf(ViewModeSwitchItemListInterface::class, $field);
      $this->assertEquals($expected, $field->getName());
    }
  }

  /**
   * Data provider for testing view mode switch field candidate names.
   *
   * @return array
   *   The field candidate names data to test.
   */
  public function dataProviderGetFieldCandidateNames(): array {
    return [
      [
        'entity_type_foo',
        'bundle_foo_foo',
        [
          'field_foo_vms',
        ],
      ],
      [
        'entity_type_bar',
        'bundle_bar_bar',
        [
          'field_bar_vms1',
          'field_bar_vms2',
        ],
      ],
      [
        'entity_type_bar',
        'bundle_bar_foobar',
        [
          'field_bar_vms2',
        ],
      ],
      [
        'entity_type_bar',
        'bundle_bar_unknown',
        [],
      ],
      [
        'entity_type_foobar',
        'bundle_foobar_foobar',
        [],
      ],
      [
        'entity_type_unknown',
        'bundle_unknown',
        [],
      ],
    ];
  }

  /**
   * Tests getting view mode switch field candidate names for a given entity.
   *
   * @covers ::getFieldCandidateNames
   *
   * @dataProvider dataProviderGetFieldCandidateNames
   */
  public function testGetFieldCandidateNames(string $entity_type_id, string $bundle, array $expected): void {
    // Prepare test entity.
    $entity = $this->createMock(FieldableEntityInterface::class);

    $entity->expects($this->atLeastOnce())
      ->method('getEntityTypeId')
      ->willReturn($entity_type_id);

    $entity->expects($this->atLeastOnce())
      ->method('bundle')
      ->willReturn($bundle);

    $this->assertEquals($expected, $this->viewModeSwitchEntityFieldManager->getFieldCandidateNames($entity));
  }

  /**
   * Tests getting a lightweight map of view mode switch fields across bundles.
   *
   * @covers ::getFieldMap
   */
  public function testGetFieldMap(): void {
    $expected = [
      'entity_type_foo' => [
        'field_foo_vms' => [
          'type' => 'view_mode_switch',
          'bundles' => [
            'bundle_foo_foo' => 'bundle_foo_foo',
            'bundle_foo_bar' => 'bundle_foo_bar',
          ],
        ],
      ],
      'entity_type_bar' => [
        'field_bar_vms1' => [
          'type' => 'view_mode_switch',
          'bundles' => [
            'bundle_bar_bar' => 'bundle_bar_bar',
          ],
        ],
        'field_bar_vms2' => [
          'type' => 'view_mode_switch',
          'bundles' => [
            'bundle_bar_bar' => 'bundle_bar_bar',
            'bundle_bar_foobar' => 'bundle_bar_foobar',
          ],
        ],
      ],
    ];

    $this->assertEquals($expected, $this->viewModeSwitchEntityFieldManager->getFieldMap());
  }

  /**
   * Data provider for view mode switch field storages using origin view mode.
   *
   * @return array
   *   The test data.
   */
  public function dataProviderGetFieldStorageDefinitionsUsingOriginViewMode(): array {
    $origin_view_modes_per_field = [
      'entity_type_foo.field_foo_vms' => [
        'default' => 'default',
        'foo' => 'foo',
      ],
      'entity_type_bar.field_bar_vms1' => [],
      'entity_type_bar.field_bar_vms2' => [
        'default' => 'default',
        'bar' => 'bar',
      ],
    ];

    return [
      [
        'bar',
        $origin_view_modes_per_field,
        [
          'entity_type_bar.field_bar_vms2',
        ],
      ],
      [
        'foo',
        $origin_view_modes_per_field,
        [
          'entity_type_foo.field_foo_vms',
        ],
      ],
      [
        'default',
        $origin_view_modes_per_field,
        [
          'entity_type_foo.field_foo_vms',
          'entity_type_bar.field_bar_vms2',
        ],
      ],
      [
        'unknown',
        $origin_view_modes_per_field,
        [],
      ],
    ];
  }

  /**
   * Tests getting all view mode switch field storages using origin view mode.
   *
   * @param string $view_mode
   *   The view mode to test.
   * @param array $origin_view_modes
   *   The origin view modes configured for the field storages to test.
   * @param array $expected
   *   The expected results.
   *
   * @covers ::getFieldStorageDefinitionsUsingOriginViewMode
   *
   * @dataProvider dataProviderGetFieldStorageDefinitionsUsingOriginViewMode
   */
  public function testGetFieldStorageDefinitionsUsingOriginViewMode(string $view_mode, array $origin_view_modes, array $expected): void {
    $storage = $this->createMock(ConfigEntityStorageInterface::class);

    $storage->expects($this->any())
      ->method('load')
      ->willReturnCallback(function ($id) use ($origin_view_modes) {
        $field_storage_config = $this->createMock(FieldStorageConfigInterface::class);

        $field_storage_config->expects($this->atLeastOnce())
          ->method('getSetting')
          ->with('origin_view_modes')
          ->willReturnCallback(function () use ($id, $origin_view_modes) {
            return !empty($origin_view_modes[$id]) ? $origin_view_modes[$id] : [];
          });

        $field_storage_config->expects($this->any())
          ->method('id')
          ->willReturn($id);

        return $field_storage_config;
      });

    $this->entityTypeManager->expects($this->any())
      ->method('getStorage')
      ->with('field_storage_config')
      ->willReturn($storage);

    $field_storage_definitions = $this->viewModeSwitchEntityFieldManager->getFieldStorageDefinitionsUsingOriginViewMode($view_mode);
    $this->assertEquals($expected, array_keys($field_storage_definitions));
    $this->assertContainsOnlyInstancesOf(FieldStorageConfigInterface::class, $field_storage_definitions);
  }

  /**
   * Tests getting all view mode switch field storages without origin view mode.
   *
   * @covers ::getFieldStorageDefinitionsWithoutOriginViewMode
   */
  public function testGetFieldStorageDefinitionsWithoutOriginViewMode(): void {
    $storage = $this->createMock(ConfigEntityStorageInterface::class);

    $storage->expects($this->any())
      ->method('load')
      ->willReturnCallback(function ($id) {
        $field_storage_config = $this->createMock(FieldStorageConfigInterface::class);

        $field_storage_config->expects($this->atLeastOnce())
          ->method('getSetting')
          ->with('origin_view_modes')
          ->willReturnCallback(function () use ($id) {
            switch ($id) {
              case 'entity_type_foo.field_foo_vms':
                return [
                  'foo',
                  'bar',
                ];

              case 'entity_type_bar.field_bar_vms2':
                return [
                  'foo',
                ];

              default:
                return [];
            }
          });

        $field_storage_config->expects($this->any())
          ->method('id')
          ->willReturn($id);

        return $field_storage_config;
      });

    $this->entityTypeManager->expects($this->any())
      ->method('getStorage')
      ->with('field_storage_config')
      ->willReturn($storage);

    $field_storage_definitions = $this->viewModeSwitchEntityFieldManager->getFieldStorageDefinitionsWithoutOriginViewMode();
    $this->assertEquals(['entity_type_bar.field_bar_vms1'], array_keys($field_storage_definitions));
    $this->assertContainsOnlyInstancesOf(FieldStorageConfigInterface::class, $field_storage_definitions);
  }

  /**
   * Data provider for testing all responsible fields for given view mode.
   *
   * @return array
   *   The test data.
   */
  public function dataProviderGetResponsibleFields(): array {
    return [
      'with-id-all-responsible' => [
        1,
        [
          'field_bar_vms1' => TRUE,
          'field_bar_vms2' => TRUE,
        ],
        [
          'field_bar_vms1',
          'field_bar_vms2',
        ],
      ],
      'with-id-second-responsible' => [
        2,
        [
          'field_bar_vms2' => TRUE,
        ],
        [
          'field_bar_vms2',
        ],
      ],
      'with-id-none-responsible' => [
        3,
        [],
        [],
      ],
      'without-id-all-responsible' => [
        NULL,
        [
          'field_bar_vms1' => TRUE,
          'field_bar_vms2' => TRUE,
        ],
        [
          'field_bar_vms1',
          'field_bar_vms2',
        ],
      ],
    ];
  }

  /**
   * Tests getting all responsible view mode switch fields for given view mode.
   *
   * @param int|null $entity_id
   *   An optional entity ID.
   * @param array $responsible
   *   Test data which field is applicable.
   * @param array $expected
   *   The expected results.
   *
   * @covers ::getResponsibleFields
   *
   * @dataProvider dataProviderGetResponsibleFields
   */
  public function testGetResponsibleFields(?int $entity_id, array $responsible, array $expected): void {
    // Prepare fieldable test entity.
    $entity = $this->createMock(FieldableEntityInterface::class);

    $entity->expects($this->atLeastOnce())
      ->method('getEntityTypeId')
      ->willReturn('entity_type_bar');

    $entity->expects($this->atLeastOnce())
      ->method('bundle')
      ->willReturn('bundle_bar_bar');

    $entity->expects($this->atLeastOnce())
      ->method('id')
      ->willReturn($entity_id);

    $entity->expects($this->any())
      ->method('get')
      ->willReturnCallback(function ($field_name) use ($responsible) {
        // Prepare test field item list mock.
        $field = $this->getMockBuilder(ViewModeSwitchItemList::class)
          ->onlyMethods([
            'isResponsible',
          ])
          ->disableOriginalConstructor()
          ->getMock();

        $field->expects($this->any())
          ->method('isResponsible')
          ->willReturnCallback(function () use ($field_name, $responsible) {
            return !empty($responsible[$field_name]);
          });

        return $field;
      });

    $fields = $this->viewModeSwitchEntityFieldManager->getResponsibleFields($entity, 'foo_responsible');

    $this->assertEquals($expected, array_keys($fields));
    $this->assertContainsOnlyInstancesOf(ViewModeSwitchItemListInterface::class, $fields);
  }

  /**
   * Data provider for removing origin view mode from field storage configs.
   *
   * @return array
   *   The test data.
   */
  public function dataProviderRemoveOriginViewModeFromFieldStorageConfigs(): array {
    return [
      'config-contains-origin-view-mode' => [
        'foo',
        [
          'foo' => 'foo',
          'bar' => 'bar',
        ],
        [
          'bar' => 'bar',
        ],
      ],
      'config-not-contains-origin-view-mode' => [
        'bar',
        [
          'foo' => 'foo',
          'baz' => 'baz',
        ],
        [
          'foo' => 'foo',
          'baz' => 'baz',
        ],
      ],
    ];
  }

  /**
   * Tests removing given origin view mode from field storage configs.
   *
   * @param string $view_mode
   *   The view mode to test.
   * @param array $origin_view_modes
   *   The origin view modes configured for the field storages to test.
   * @param array $expected
   *   The expected results.
   *
   * @covers ::removeOriginViewModeFromFieldStorageConfigs
   *
   * @dataProvider dataProviderRemoveOriginViewModeFromFieldStorageConfigs
   */
  public function testRemoveOriginViewModeFromFieldStorageConfigs(string $view_mode, array $origin_view_modes, array $expected): void {
    $storage = $this->createMock(ConfigEntityStorageInterface::class);

    $storage->expects($this->any())
      ->method('load')
      ->willReturnCallback(function ($id) use ($view_mode, $origin_view_modes, $expected) {
        $field_storage_config_requires_removal = isset($origin_view_modes[$view_mode]);

        $field_storage_config = $this->getMockBuilder(FieldStorageConfig::class)
          ->onlyMethods([
            'getSetting',
            'id',
            'save',
            'setSetting',
          ])
          ->disableOriginalConstructor()
          ->getMock();

        $field_storage_config->expects($this->any())
          ->method('id')
          ->willReturn($id);

        $field_storage_config->expects($this->atLeastOnce())
          ->method('getSetting')
          ->with('origin_view_modes')
          ->willReturn($origin_view_modes);

        $field_storage_config->expects($field_storage_config_requires_removal ? $this->once() : $this->never())
          ->method('setSetting')
          ->with('origin_view_modes', $expected);

        $field_storage_config->expects($field_storage_config_requires_removal ? $this->once() : $this->never())
          ->method('save');

        return $field_storage_config;
      });

    $this->entityTypeManager->expects($this->any())
      ->method('getStorage')
      ->with('field_storage_config')
      ->willReturn($storage);

    $this->viewModeSwitchEntityFieldManager->removeOriginViewModeFromFieldStorageConfigs($view_mode);
  }

}

// @codingStandardsIgnoreStart
/**
 * Mocked view mode switch entity field manager service class for tests.
 */
class EntityFieldManager_Test extends EntityFieldManager {

  /**
   * {@inheritdoc}
   */
  public function getFieldCandidateNames(FieldableEntityInterface $entity): array {
    return parent::getFieldCandidateNames($entity);
  }

}
// @codingStandardsIgnoreEnd
