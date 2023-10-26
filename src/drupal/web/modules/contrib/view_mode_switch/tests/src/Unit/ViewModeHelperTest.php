<?php

namespace Drupal\Tests\view_mode_switch\Unit;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityViewModeInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\view_mode_switch\Entity\EntityFieldManagerInterface;
use Drupal\view_mode_switch\ViewModeHelper;

/**
 * Tests the view mode helper for the view mode switch field type.
 *
 * @group view_mode_switch
 * @coversDefaultClass \Drupal\view_mode_switch\ViewModeHelper
 */
class ViewModeHelperTest extends UnitTestCase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * A test view mode.
   *
   * @var \Drupal\Core\Entity\EntityViewModeInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $viewMode;

  /**
   * The view mode helper service.
   *
   * @var \Drupal\view_mode_switch\ViewModeHelperInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $viewModeHelper;

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

    // Create required service mocks.
    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $this->viewModeSwitchEntityFieldManager = $this->createMock(EntityFieldManagerInterface::class);
    $this->viewMode = $this->createMock(EntityViewModeInterface::class);

    // Create view mode helper service mock.
    $this->viewModeHelper = $this->getMockBuilder(ViewModeHelper::class)
      ->onlyMethods([])
      ->setConstructorArgs([
        $this->viewModeSwitchEntityFieldManager,
        $this->entityTypeManager,
      ])
      ->getMock();
  }

  /**
   * Tests getting the view mode name based on its ID.
   *
   * @covers ::getName
   */
  public function testGetName(): void {
    // Prepare view mode.
    $this->viewMode->expects($this->once())
      ->method('id')
      ->willReturn('entity_test.view_mode_test');

    $this->assertEquals('view_mode_test', $this->viewModeHelper->getName($this->viewMode));
  }

  /**
   * Tests origin view mode deletion changing view mode switch field storages.
   *
   * @covers ::preDelete
   */
  public function testPreDelete(): void {
    $view_mode_name = 'view_mode_test';

    // Prepare view mode.
    $this->viewMode->expects($this->once())
      ->method('id')
      ->willReturn('entity_test.' . $view_mode_name);

    // Prepare view mode switch entity field manager.
    $this->viewModeSwitchEntityFieldManager->expects($this->once())
      ->method('removeOriginViewModeFromFieldStorageConfigs')
      ->with($view_mode_name);

    $this->viewModeHelper->preDelete($this->viewMode);
  }

}
