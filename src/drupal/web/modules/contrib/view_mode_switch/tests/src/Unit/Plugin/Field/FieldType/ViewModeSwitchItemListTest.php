<?php

namespace Drupal\Tests\view_mode_switch\Unit\Plugin\Field\FieldType;

use Drupal\Tests\UnitTestCase;
use Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemInterface;
use Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemList;

/**
 * Tests the item list class for view mode switch fields.
 *
 * @group view_mode_switch
 * @coversDefaultClass \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemList
 */
class ViewModeSwitchItemListTest extends UnitTestCase {

  /**
   * The view mode switch field item list.
   *
   * @var \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemListInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $viewModeSwitchItemList;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Prepare view mode switch field item list mock.
    $this->viewModeSwitchItemList = $this->getMockBuilder(ViewModeSwitchItemList::class)
      ->onlyMethods([
        'first',
      ])
      ->disableOriginalConstructor()
      ->getMock();
  }

  /**
   * Data provider for getting the view modes allowed to switch to.
   *
   * @return array
   *   The test data.
   */
  public function dataProviderGetAllowedViewModes(): array {
    return [
      'returns-view-modes' => [
        [
          'foo' => 'foo',
          'bar' => 'bar',
        ],
        [
          'foo' => 'foo',
          'bar' => 'bar',
        ],
      ],
      'returns-no-view-modes' => [
        [],
        [],
      ],
    ];
  }

  /**
   * Tests getting the view modes allowed to switch to.
   *
   * @param array $allowed_view_modes
   *   The allowed view modes to return (if any).
   * @param array $expected
   *   The expected results.
   *
   * @covers ::getAllowedViewModes
   *
   * @dataProvider dataProviderGetAllowedViewModes
   */
  public function testGetAllowedViewModes(array $allowed_view_modes, array $expected): void {
    // Prepare field item mock.
    $item = $this->createMock(ViewModeSwitchItemInterface::class);

    $item->expects($this->once())
      ->method('getAllowedViewModes')
      ->willReturn($allowed_view_modes);

    // Prepare view mode switch field item list mock.
    $this->viewModeSwitchItemList->expects($this->once())
      ->method('first')
      ->willReturn($item);

    $this->assertEquals($expected, $this->viewModeSwitchItemList->getAllowedViewModes());
  }

  /**
   * Data provider for getting a representative view mode switch field item.
   *
   * @return array
   *   The test data.
   */
  public function dataProviderGetRepresentativeItem(): array {
    return [
      [TRUE],
      [FALSE],
    ];
  }

  /**
   * Tests getting a representative view mode switch field item.
   *
   * @param bool $is_empty
   *   Whether the field item list is empty.
   *
   * @covers ::getRepresentativeItem
   *
   * @dataProvider dataProviderGetRepresentativeItem
   */
  public function testGetRepresentativeItem(bool $is_empty): void {
    // Prepare view mode switch field item list mock.
    $item_list = $this->getMockBuilder(ViewModeSwitchItemList_Test::class)
      ->onlyMethods([
        'createItem',
        'first',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $item_list->expects($this->once())
      ->method('first')
      ->willReturnCallback(function () use ($is_empty) {
        if ($is_empty) {
          return NULL;
        }

        return $this->createMock(ViewModeSwitchItemInterface::class);
      });

    $item_list->expects($is_empty ? $this->once() : $this->never())
      ->method('createItem')
      ->willReturn($this->createMock(ViewModeSwitchItemInterface::class));

    $this->assertInstanceOf(ViewModeSwitchItemInterface::class, $item_list->getRepresentativeItem());
  }

  /**
   * Data provider for getting the view modes to switch.
   *
   * @return array
   *   The test data.
   */
  public function dataProviderGetOriginViewModes(): array {
    return [
      'returns-view-modes' => [
        [
          'foo' => 'foo',
          'bar' => 'bar',
        ],
        [
          'foo' => 'foo',
          'bar' => 'bar',
        ],
      ],
      'returns-no-view-modes' => [
        [],
        [],
      ],
    ];
  }

  /**
   * Tests getting the view modes to switch.
   *
   * @param array $origin_view_modes
   *   The origin view modes to return (if any).
   * @param array $expected
   *   The expected results.
   *
   * @covers ::getOriginViewModes
   *
   * @dataProvider dataProviderGetOriginViewModes
   */
  public function testGetOriginViewModes(array $origin_view_modes, array $expected): void {
    // Prepare field item mock.
    $item = $this->createMock(ViewModeSwitchItemInterface::class);

    $item->expects($this->once())
      ->method('getOriginViewModes')
      ->willReturn($origin_view_modes);

    // Prepare view mode switch field item list mock.
    $this->viewModeSwitchItemList->expects($this->once())
      ->method('first')
      ->willReturn($item);

    $this->assertEquals($expected, $this->viewModeSwitchItemList->getOriginViewModes());
  }

  /**
   * Data provider for getting the view mode to switch to.
   *
   * @return array
   *   The test data.
   */
  public function dataProviderGetViewMode(): array {
    return [
      'returns-view-mode' => [
        'foo',
        'foo',
      ],
      'returns-no-view-mode' => [
        NULL,
        NULL,
      ],
    ];
  }

  /**
   * Tests getting the view mode to switch to.
   *
   * @param string|null $view_mode
   *   The view mode to return (if any).
   * @param string|null $expected
   *   The expected result.
   *
   * @covers ::getViewMode
   *
   * @dataProvider dataProviderGetViewMode
   */
  public function testGetViewMode(?string $view_mode, ?string $expected): void {
    // Prepare field item mock.
    $item = $this->createMock(ViewModeSwitchItemInterface::class);

    $item->expects($this->once())
      ->method('getViewMode')
      ->willReturn($view_mode);

    // Prepare view mode switch field item list mock.
    $this->viewModeSwitchItemList->expects($this->once())
      ->method('first')
      ->willReturn($item);

    $this->assertEquals($expected, $this->viewModeSwitchItemList->getViewMode());
  }

  /**
   * Data provider for whether a field is applicable for an origin view mode.
   *
   * @return array
   *   The test data.
   */
  public function dataProviderIsApplicable(): array {
    return [
      'is-applicable' => [
        TRUE,
        TRUE,
      ],
      'is-not-applicable' => [
        FALSE,
        FALSE,
      ],
    ];
  }

  /**
   * Tests whether a field is applicable for the given origin view mode.
   *
   * @param bool $is_applicable
   *   Whether the field is applicable.
   * @param bool $expected
   *   The expected result.
   *
   * @covers ::isApplicable
   *
   * @dataProvider dataProviderIsApplicable
   */
  public function testIsApplicable(bool $is_applicable, bool $expected): void {
    // Prepare field item mock.
    $item = $this->createMock(ViewModeSwitchItemInterface::class);

    $item->expects($this->once())
      ->method('isApplicable')
      ->with('foo')
      ->willReturn($is_applicable);

    // Prepare view mode switch field item list mock.
    $this->viewModeSwitchItemList->expects($this->once())
      ->method('first')
      ->willReturn($item);

    $this->assertEquals($expected, $this->viewModeSwitchItemList->isApplicable('foo'));
  }

  /**
   * Data provider for whether a field is responsible for an origin view mode.
   *
   * @return array
   *   The test data.
   */
  public function dataProviderIsResponsible(): array {
    return [
      'is-responsible' => [
        TRUE,
        TRUE,
      ],
      'is-not-responsible' => [
        FALSE,
        FALSE,
      ],
    ];
  }

  /**
   * Tests whether the field is responsible for the given origin view mode.
   *
   * @param bool $is_responsible
   *   Whether the field is responsible.
   * @param bool $expected
   *   The expected result.
   *
   * @covers ::isResponsible
   *
   * @dataProvider dataProviderIsResponsible
   */
  public function testIsResponsible(bool $is_responsible, bool $expected): void {
    // Prepare field item mock.
    $item = $this->createMock(ViewModeSwitchItemInterface::class);

    $item->expects($this->once())
      ->method('isResponsible')
      ->with('foo')
      ->willReturn($is_responsible);

    // Prepare view mode switch field item list mock.
    $this->viewModeSwitchItemList->expects($this->once())
      ->method('first')
      ->willReturn($item);

    $this->assertEquals($expected, $this->viewModeSwitchItemList->isResponsible('foo'));
  }

}

// @codingStandardsIgnoreStart
/**
 * Mocked view mode switch field item list class for tests.
 */
class ViewModeSwitchItemList_Test extends ViewModeSwitchItemList {

  /**
   * {@inheritdoc}
   */
  public function getRepresentativeItem(): ViewModeSwitchItemInterface {
    return parent::getRepresentativeItem();
  }

}
// @codingStandardsIgnoreEnd
