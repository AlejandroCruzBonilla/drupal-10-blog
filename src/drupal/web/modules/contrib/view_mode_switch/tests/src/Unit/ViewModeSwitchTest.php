<?php

namespace Drupal\Tests\view_mode_switch\Unit;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityViewModeInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Link;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\view_mode_switch\Entity\EntityFieldManagerInterface;
use Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemListInterface;
use Drupal\view_mode_switch\ViewModeSwitch;

/**
 * Tests the view mode switch service.
 *
 * @group view_mode_switch
 * @coversDefaultClass \Drupal\view_mode_switch\ViewModeSwitch
 */
class ViewModeSwitchTest extends UnitTestCase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * The logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $logger;

  /**
   * The view mode switch service.
   *
   * @var \Drupal\view_mode_switch\ViewModeSwitchInterface|\Drupal\Tests\view_mode_switch\Unit\ViewModeSwitch_Test|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $viewModeSwitch;

  /**
   * The view mode switch entity field manager.
   *
   * @var \Drupal\view_mode_switch\Entity\EntityFieldManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $viewModeSwitchEntityFieldManager;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Create/register string translation mock.
    $container = new ContainerBuilder();
    $container->set('string_translation', $this->getStringTranslationStub());
    \Drupal::setContainer($container);

    // Create required service mocks.
    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $this->logger = $this->createMock(LoggerChannelFactoryInterface::class);
    $this->viewModeSwitchEntityFieldManager = $this->createMock(EntityFieldManagerInterface::class);

    // Create default view mode switch service mock.
    $this->viewModeSwitch = $this->setUpViewModeSwitchMock();
  }

  /**
   * Returns a configured view mode switch service mock.
   *
   * @param array $only_methods
   *   An array of names for methods to be configurable.
   *
   * @return \Drupal\Tests\view_mode_switch\Unit\ViewModeSwitch_Test|\PHPUnit\Framework\MockObject\MockObject
   *   The view mode switch service mock.
   */
  protected function setUpViewModeSwitchMock(array $only_methods = []): ViewModeSwitch_Test {
    return $this->getMockBuilder(ViewModeSwitch_Test::class)
      ->onlyMethods($only_methods)
      ->setConstructorArgs([
        $this->viewModeSwitchEntityFieldManager,
        $this->entityTypeManager,
        $this->logger,
      ])
      ->getMock();
  }

  /**
   * Data provider for testing internal method to get a view mode to switch to.
   *
   * @return array
   *   The test data.
   */
  public function dataProviderDoGetViewModeToSwitchTo(): array {
    return [
      'no-applicable-field' => [
        'foo',
        [],
        [],
        NULL,
      ],
      'with-applicable-field' => [
        'foo',
        [
          'foo' => [
            'field_name' => 'field_vms',
            'origin_view_mode' => 'foo',
            'target_view_mode' => 'bar',
          ],
        ],
        [],
        'bar',
      ],
      'with-subsequent-applicable-fields' => [
        'foo',
        [
          'foo' => [
            'field_name' => 'field_vms1',
            'origin_view_mode' => 'foo',
            'target_view_mode' => 'bar',
          ],
          'bar' => [
            'field_name' => 'field_vms2',
            'origin_view_mode' => 'bar',
            'target_view_mode' => 'baz',
          ],
          'baz' => [
            'field_name' => 'field_vms3',
            'origin_view_mode' => 'baz',
            'target_view_mode' => 'foobar',
          ],
        ],
        [],
        'foobar',
      ],
      'with-subsequent-applicable-fields-recursion' => [
        'foo',
        [
          'foo' => [
            'field_name' => 'field_vms1',
            'origin_view_mode' => 'foo',
            'target_view_mode' => 'bar',
          ],
          'bar' => [
            'field_name' => 'field_vms2',
            'origin_view_mode' => 'bar',
            'target_view_mode' => 'baz',
          ],
          'baz' => [
            'field_name' => 'field_vms3',
            'origin_view_mode' => 'baz',
            'target_view_mode' => 'foobar',
          ],
          'foobar' => [
            'field_name' => 'field_vms4',
            'origin_view_mode' => 'foobar',
            'target_view_mode' => 'bar',
          ],
        ],
        [
          '%origin_view_mode' => 'foo',
          '%view_mode_switches' => 'foo › bar › baz › foobar › bar',
          'link' => '/path/to/entity',
        ],
        'baz',
      ],
    ];
  }

  /**
   * Tests the internal method to get a view mode to switch to (if applicable).
   *
   * @param string $origin_view_mode
   *   The origin view mode to start from.
   * @param array $view_mode_switches
   *   An array of view mode switches to perform.
   * @param array $expected_recursion_warning_args
   *   Any expected arguments when logging recursion.
   * @param string|null $expected_target_view_mode
   *   The expected target view mode.
   *
   * @covers ::doGetViewModeToSwitchTo
   *
   * @dataProvider dataProviderDoGetViewModeToSwitchTo
   */
  public function testDoGetViewModeToSwitchTo(string $origin_view_mode, array $view_mode_switches, array $expected_recursion_warning_args, ?string $expected_target_view_mode): void {
    $entity_type_id = 'entity_test';
    $count_view_mode_switches = count($view_mode_switches);

    // Prepare entity view mode mock.
    $entity_view_mode = $this->createMock(EntityViewModeInterface::class);

    // Prepare entity view mode storage mock.
    $entity_view_mode_storage = $this->createMock(ConfigEntityStorageInterface::class);

    $entity_view_mode_storage->expects($count_view_mode_switches ? $this->exactly($count_view_mode_switches) : $this->never())
      ->method('load')
      ->willReturn($entity_view_mode);

    // Prepare entity type manager mock.
    $this->entityTypeManager->expects($count_view_mode_switches ? $this->exactly($count_view_mode_switches) : $this->never())
      ->method('getStorage')
      ->with('entity_view_mode')
      ->willReturn($entity_view_mode_storage);

    // Prepare entity link mock.
    $entity_link = $this->createMock(Link::class);

    $entity_link->expects(!empty($expected_recursion_warning_args['link']) ? $this->once() : $this->never())
      ->method('toString')
      ->willReturn(!empty($expected_recursion_warning_args['link']) ? $expected_recursion_warning_args['link'] : NULL);

    // Prepare entity mock.
    $entity = $this->createMock(FieldableEntityInterface::class);

    $entity->expects($count_view_mode_switches ? $this->atLeastOnce() : $this->never())
      ->method('getEntityTypeId')
      ->willReturn($entity_type_id);

    $entity->expects($count_view_mode_switches ? $this->atLeastOnce() : $this->never())
      ->method('addCacheableDependency')
      ->with($entity_view_mode);

    $entity->expects($expected_recursion_warning_args ? $this->once() : $this->never())
      ->method('toLink')
      ->with('View')
      ->willReturn($entity_link);

    // Prepare view mode switch entity field manager mock.
    $get_applicable_field_consecutive_args = [];

    if ($count_view_mode_switches) {
      foreach ($view_mode_switches as $view_mode_switch) {
        $get_applicable_field_consecutive_args[] = [
          $entity,
          $view_mode_switch['origin_view_mode'],
        ];
      }
    }

    $get_applicable_field = $this->viewModeSwitchEntityFieldManager->expects($count_view_mode_switches ? $this->atLeastOnce() : $this->once())
      ->method('getApplicableField');
    call_user_func_array([$get_applicable_field, 'withConsecutive'], $get_applicable_field_consecutive_args);
    $get_applicable_field
      ->willReturnCallback(function (FieldableEntityInterface $entity, $view_mode) use ($view_mode_switches) {
        if (!empty($view_mode_switches[$view_mode]['field_name']) && !empty($view_mode_switches[$view_mode]['target_view_mode'])) {
          $view_mode_switch = $view_mode_switches[$view_mode];

          $field = $this->createMock(ViewModeSwitchItemListInterface::class);

          $field->expects($this->once())
            ->method('getName')
            ->willReturn($view_mode_switch['field_name']);

          $field->expects($this->once())
            ->method('getViewMode')
            ->willReturn($view_mode_switch['target_view_mode']);

          return $field;
        }

        return NULL;
      });

    // Prepare logger channel mock.
    $logger_channel = $this->createMock(LoggerChannelInterface::class);

    $logger_channel->expects($expected_recursion_warning_args ? $this->once() : $this->never())
      ->method('warning')
      ->with('Recursion detected when trying to switch %origin_view_mode view mode via %view_mode_switches.', $expected_recursion_warning_args);

    // Prepare logger channel factory mock.
    $this->logger->expects($expected_recursion_warning_args ? $this->once() : $this->never())
      ->method('get')
      ->with('view_mode_switch')
      ->willReturn($logger_channel);

    $this->assertEquals($expected_target_view_mode, $this->viewModeSwitch->doGetViewModeToSwitchTo($entity, $origin_view_mode));
  }

  /**
   * Data provider for getting the view mode to switch to (if applicable).
   *
   * @return array
   *   The test data.
   */
  public function dataProviderGetViewModeToSwitchTo(): array {
    return [
      'with-id' => [
        'entity_type_test',
        1,
        'foo',
        'bar',
      ],
      'without-id' => [
        'entity_type_test',
        NULL,
        'foo',
        'barbaz',
      ],
      'with-id-but-no-switch' => [
        'entity_type_test',
        2,
        'foo',
        NULL,
      ],
      'paragraph-entity' => [
        'paragraph',
        4,
        'foo',
        'foobar',
      ],
    ];
  }

  /**
   * Tests getting the view mode to switch to (if applicable).
   *
   * @param string $entity_type_id
   *   The type to use for the entity ID.
   * @param int|null $entity_id
   *   A numeric entity ID or NULL to automatically use the UUID as fallback.
   * @param string $origin_view_mode
   *   The origin view mode to start from.
   * @param string|null $expected_target_view_mode
   *   The expected targed view mode.
   *
   * @covers ::getViewModeToSwitchTo
   *
   * @dataProvider dataProviderGetViewModeToSwitchTo
   */
  public function testGetViewModeToSwitchTo(string $entity_type_id, ?int $entity_id, string $origin_view_mode, ?string $expected_target_view_mode): void {
    $bundle = 'bundle_test';

    // Create entity mock.
    $entity = $this->createMock(FieldableEntityInterface::class);

    $entity->expects($this->exactly(2))
      ->method('getEntityTypeId')
      ->willReturn($entity_type_id);

    $entity->expects($this->exactly(2))
      ->method('bundle')
      ->willReturn($bundle);

    $entity->expects($this->exactly(2))
      ->method('id')
      ->willReturn($entity_id);

    // Create and configure custom view mode switch service mock.
    $view_mode_switch = $this->setUpViewModeSwitchMock([
      'doGetViewModeToSwitchTo',
    ]);

    $view_mode_switch->expects($this->once())
      ->method('doGetViewModeToSwitchTo')
      ->with($entity, $origin_view_mode)
      ->willReturn($expected_target_view_mode);

    $this->assertEquals($expected_target_view_mode, $view_mode_switch->getViewModeToSwitchTo($entity, $origin_view_mode));
    $this->assertEquals($expected_target_view_mode, $view_mode_switch->getViewModeToSwitchTo($entity, $origin_view_mode));
  }

}

// @codingStandardsIgnoreStart
/**
 * Mocked view mode switch service class for tests.
 */
class ViewModeSwitch_Test extends ViewModeSwitch {

  /**
   * {@inheritdoc}
   */
  public function doGetViewModeToSwitchTo(FieldableEntityInterface $entity, $view_mode, array $results = []): ?string {
    return parent::doGetViewModeToSwitchTo($entity, $view_mode, $results);
  }

}
// @codingStandardsIgnoreEnd
