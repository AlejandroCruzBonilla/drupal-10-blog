<?php

namespace Drupal\Tests\view_mode_switch\Unit\Plugin\Field\FieldType;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItem;

/**
 * Tests the view mode switch field type.
 *
 * @group view_mode_switch
 * @coversDefaultClass \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItem
 */
class ViewModeSwitchItemTest extends UnitTestCase {

  /**
   * The view mode switch field item.
   *
   * @var \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $viewModeSwitchItem;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Create/register string translation mock.
    $container = new ContainerBuilder();
    $container->set('string_translation', $this->getStringTranslationStub());
    \Drupal::setContainer($container);

    // Prepare field definition.
    $field_definition = $this->createMock(FieldDefinitionInterface::class);
    $field_definition->expects($this->any())
      ->method('getTargetEntityTypeId')
      ->willReturn('entity_test');

    // Prepare entity display repository mock.
    $entity_display_repository = $this->createMock(EntityDisplayRepositoryInterface::class);
    $entity_display_repository->expects($this->any())
      ->method('getViewModeOptions')
      ->willReturn([
        'bar' => 'To: Bar',
        'baz' => 'To: Baz',
        'default' => 'Origin: Default',
        'foo' => 'Origin: Foo',
        'foobar' => 'Origin: FooBar',
      ]);

    // Prepare view mode switch item mock.
    $this->viewModeSwitchItem = $this->getMockBuilder(ViewModeSwitchItem::class)
      ->onlyMethods([
        '__get',
        'entityDisplayRepository',
        'getFieldDefinition',
        'getSetting',
        'isEmpty',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $this->viewModeSwitchItem
      ->expects($this->any())
      ->method('entityDisplayRepository')
      ->willReturn($entity_display_repository);

    $this->viewModeSwitchItem
      ->expects($this->any())
      ->method('getFieldDefinition')
      ->willReturn($field_definition);

    $this->viewModeSwitchItem
      ->expects($this->any())
      ->method('getSetting')
      ->willReturnCallback(function ($setting) {
        switch ($setting) {
          case 'allowed_view_modes':
            return [
              'bar' => 'bar',
              'baz' => 'baz',
            ];

          case 'origin_view_modes':
            return [
              'default' => 'default',
              'foo' => 'foo',
            ];

          default:
            return [];
        }
      });
  }

  /**
   * Tests the field-level settings for the field plugin.
   *
   * @covers ::defaultFieldSettings
   */
  public function testDefaultFieldSettings(): void {
    $expected = [
      'allowed_view_modes' => [],
    ];

    $this->assertEquals($expected, ViewModeSwitchItem::defaultFieldSettings());
  }

  /**
   * Tests the the storage-level settings for the field plugin.
   *
   * @covers ::defaultStorageSettings
   */
  public function testDefaultFieldStorageSettings(): void {
    $expected = [
      'origin_view_modes' => [],
    ];

    $this->assertEquals($expected, ViewModeSwitchItem::defaultStorageSettings());
  }

  /**
   * Data provider for testing additional processing of settings.
   *
   * @return array
   *   The view mode arrays data to test.
   */
  public function dataProviderSettingsToConfigData(): array {
    $expected = [
      'bar' => 'bar',

    ];
    return [
      'empty-string' => [
        [
          'foo' => '',
          'bar' => 'bar',
        ],
        $expected,
      ],
      'boolean' => [
        [
          'foo' => FALSE,
          'bar' => 'bar',
        ],
        $expected,
      ],
      'null' => [
        [
          'foo' => NULL,
          'bar' => 'bar',
        ],
        $expected,
      ],
      'zero' => [
        [
          'foo' => 0,
          'bar' => 'bar',
        ],
        $expected,
      ],
      'empty-array' => [
        [],
        [],
      ],
    ];
  }

  /**
   * Tests additional processing of the field settings.
   *
   * @param array $view_modes
   *   The view modes array to test.
   * @param array $expected_view_modes
   *   The expected view modes array.
   *
   * @covers ::fieldSettingsToConfigData
   *
   * @dataProvider dataProviderSettingsToConfigData
   */
  public function testFieldSettingsToConfigData(array $view_modes, array $expected_view_modes): void {
    $settings = [
      'foo' => FALSE,
      'allowed_view_modes' => $view_modes,
    ];

    $expected = [
      'foo' => FALSE,
      'allowed_view_modes' => $expected_view_modes,
    ];

    $this->assertEquals($expected, ViewModeSwitchItem::fieldSettingsToConfigData($settings));
  }

  /**
   * Tests getting the view modes allowed to switch to.
   *
   * @covers ::getAllowedViewModes
   */
  public function testGetAllowedViewModes(): void {
    $expected = [
      'bar' => 'bar',
      'baz' => 'baz',
    ];

    $this->assertEquals($expected, $this->viewModeSwitchItem->getAllowedViewModes());
  }

  /**
   * Tests getting the view modes to switch.
   *
   * @covers ::getOriginViewModes
   */
  public function testGetOriginViewModes(): void {
    $expected = [
      'default' => 'default',
      'foo' => 'foo',
    ];

    $this->assertEquals($expected, $this->viewModeSwitchItem->getOriginViewModes());
  }

  /**
   * Tests getting an array of possible options.
   *
   * @covers ::getPossibleOptions
   */
  public function testGetPossibleOptions(): void {
    $expected = [
      'default' => 'Origin: Default',
      'foo' => 'Origin: Foo',
      'foobar' => 'Origin: FooBar',
      'bar' => 'To: Bar',
      'baz' => 'To: Baz',
    ];

    $this->assertEquals($expected, $this->viewModeSwitchItem->getPossibleOptions());
  }

  /**
   * Tests getting an array of possible values.
   *
   * @covers ::getPossibleValues
   */
  public function testGetPossibleValues(): void {
    $expected = [
      'bar',
      'baz',
      'default',
      'foo',
      'foobar',
    ];

    $this->assertEquals($expected, $this->viewModeSwitchItem->getPossibleValues());
  }

  /**
   * Data provider for getting an array of settable options.
   *
   * @return array
   *   The test data.
   */
  public function dataProviderGetSettableOptions(): array {
    return [
      'all' => [
        FALSE,
        [
          'foobar' => 'Origin: FooBar',
          'bar' => 'To: Bar',
          'baz' => 'To: Baz',
        ],
      ],
      'allowed-only' => [
        TRUE,
        [
          'bar' => 'To: Bar',
          'baz' => 'To: Baz',
        ],
      ],
    ];
  }

  /**
   * Tests getting an array of settable values with labels for display.
   *
   * @param bool $allowed_only
   *   Whether to test with allowed view modes only.
   * @param array $expected
   *   The expected options.
   *
   * @covers ::getSettableOptions
   *
   * @dataProvider dataProviderGetSettableOptions
   */
  public function testGetSettableOptions(bool $allowed_only, array $expected): void {
    $this->assertEquals($expected, $this->viewModeSwitchItem->getSettableOptions(NULL, $allowed_only));
  }

  /**
   * Data provider for getting an array of settable values.
   *
   * @return array
   *   The test data.
   */
  public function dataProviderGetSettableValues(): array {
    return [
      'all' => [
        FALSE,
        [
          'bar',
          'baz',
          'foobar',
        ],
      ],
      'allowed-only' => [
        TRUE,
        [
          'bar',
          'baz',
        ],
      ],
    ];
  }

  /**
   * Tests getting an array of settable values.
   *
   * @param bool $allowed_only
   *   Whether to test with allowed view modes only.
   * @param array $expected
   *   The expected options.
   *
   * @covers ::getSettableValues
   *
   * @dataProvider dataProviderGetSettableValues
   */
  public function testGetSettableValues(bool $allowed_only, array $expected): void {
    $this->assertEquals($expected, $this->viewModeSwitchItem->getSettableValues(NULL, $allowed_only));
  }

  /**
   * Data provider for getting the view mode to switch to.
   *
   * @return array
   *   The test data.
   */
  public function dataProviderGetViewMode(): array {
    return [
      'bar' => [
        'bar',
        'bar',
      ],
      'baz' => [
        'baz',
        'baz',
      ],
      'foo' => [
        'foo',
        NULL,
      ],
      'default' => [
        'default',
        NULL,
      ],
      'unknown' => [
        'unknown',
        NULL,
      ],
      'empty' => [
        '',
        NULL,
      ],
    ];
  }

  /**
   * Tests getting the view mode to switch to.
   *
   * @param string $value
   *   The field value to test.
   * @param string|null $expected
   *   The expected result.
   *
   * @covers ::getViewMode
   *
   * @dataProvider dataProviderGetViewMode
   */
  public function testGetViewMode(string $value, ?string $expected): void {
    // Prepare view mode switch item mock.
    $this->viewModeSwitchItem->expects($this->any())
      ->method('isEmpty')
      ->willReturn(empty($value));

    $this->viewModeSwitchItem->expects($this->any())
      ->method('__get')
      ->willReturn($value);

    $this->assertEquals($expected, $this->viewModeSwitchItem->getViewMode());
  }

  /**
   * Data provider for whether is applicable for the given origin view mode.
   *
   * @return array
   *   The test data.
   */
  public function dataProviderIsApplicable(): array {
    return [
      'default-to-bar' => [
        'default',
        'bar',
        TRUE,
      ],
      'foo-to-bar' => [
        'foo',
        'bar',
        TRUE,
      ],
      'foobar-to-bar' => [
        'foobar',
        'bar',
        FALSE,
      ],
      'bar-to-bar' => [
        'bar',
        'bar',
        FALSE,
      ],
    ];
  }

  /**
   * Tests whether a field is applicable for the given origin view mode.
   *
   * @param string $view_mode
   *   The name of the view mode to test.
   * @param string $value
   *   The field value to test.
   * @param bool $expected
   *   The expected result.
   *
   * @covers ::isApplicable
   *
   * @dataProvider dataProviderIsApplicable
   */
  public function testIsApplicable(string $view_mode, string $value, bool $expected): void {
    // Prepare view mode switch item mock.
    $this->viewModeSwitchItem->expects($this->any())
      ->method('isEmpty')
      ->willReturn(empty($value));

    $this->viewModeSwitchItem->expects($this->any())
      ->method('__get')
      ->willReturn($value);

    $this->assertEquals($expected, $this->viewModeSwitchItem->isApplicable($view_mode));
  }

  /**
   * Data provider for whether a field is responsible for a given view mode.
   *
   * @return array
   *   The test data.
   */
  public function dataProviderIsResponsible(): array {
    return [
      'default' => [
        'default',
        TRUE,
      ],
      'foo' => [
        'foo',
        TRUE,
      ],
      'bar' => [
        'bar',
        FALSE,
      ],
    ];
  }

  /**
   * Tests whether the field is responsible for the given origin view mode.
   *
   * @param string $view_mode
   *   The name of the view mode to test.
   * @param bool $expected
   *   The expected result.
   *
   * @covers ::isResponsible
   *
   * @dataProvider dataProviderIsResponsible
   */
  public function testIsResponsible(string $view_mode, bool $expected): void {
    $this->assertEquals($expected, $this->viewModeSwitchItem->isResponsible($view_mode));
  }

  /**
   * Tests the defined field item properties.
   *
   * @covers ::propertyDefinitions
   */
  public function testPropertyDefinitions(): void {
    $field_storage_definition = $this->createMock(FieldStorageDefinitionInterface::class);

    $properties = ViewModeSwitchItem::propertyDefinitions($field_storage_definition);
    $this->assertArrayHasKey('value', $properties);
    $this->assertInstanceOf(DataDefinitionInterface::class, $properties['value']);
    $this->assertEquals('string', $properties['value']->getDataType());
    $this->assertEquals('View mode', (string) $properties['value']->getLabel());
    $this->assertEquals('The name of view mode to switch to.', (string) $properties['value']->getDescription());
    $this->assertTrue($properties['value']->isRequired());
  }

  /**
   * Tests the defined schema for the field.
   *
   * @covers ::schema
   */
  public function testSchema(): void {
    $field_storage_definition = $this->createMock(FieldStorageDefinitionInterface::class);

    $schema = ViewModeSwitchItem::schema($field_storage_definition);

    $this->assertArrayHasKey('columns', $schema);
    $this->assertArrayHasKey('value', $schema['columns']);
    $this->assertEquals([
      'type' => 'varchar_ascii',
      'length' => EntityTypeInterface::ID_MAX_LENGTH,
    ], $schema['columns']['value']);
  }

  /**
   * Tests additional processing of the field storage settings.
   *
   * @param array $view_modes
   *   The view modes array to test.
   * @param array $expected_view_modes
   *   The expected view modes array.
   *
   * @covers ::storageSettingsToConfigData
   *
   * @dataProvider dataProviderSettingsToConfigData
   */
  public function testStorageSettingsToConfigData(array $view_modes, array $expected_view_modes): void {
    $settings = [
      'foo' => FALSE,
      'origin_view_modes' => $view_modes,
    ];

    $expected = [
      'foo' => FALSE,
      'origin_view_modes' => $expected_view_modes,
    ];

    $this->assertEquals($expected, ViewModeSwitchItem::storageSettingsToConfigData($settings));
  }

}
