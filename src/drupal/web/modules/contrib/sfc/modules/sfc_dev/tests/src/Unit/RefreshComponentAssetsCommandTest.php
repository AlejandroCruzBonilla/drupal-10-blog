<?php

namespace Drupal\Tests\sfc_dev\Unit;

use Drupal\Core\Asset\AssetResolverInterface;
use Drupal\sfc\ComponentInterface;
use Drupal\sfc_dev\Ajax\RefreshComponentAssetsCommand;
use Drupal\Tests\UnitTestCase;

/**
 * Tests methods provided by the refresh component assets command.
 *
 * @coversDefaultClass \Drupal\sfc\ComponentBase
 *
 * @group sfc
 */
class RefreshComponentAssetsCommandTest extends UnitTestCase {

  /**
   * Tests the ::render method.
   */
  public function testRender() {
    $component = $this->createMock(ComponentInterface::class);
    $component->method('shouldWriteAssets')->willReturn(TRUE);
    $component->expects($this->atLeastOnce())->method('writeAssets');
    $asset_resolver = $this->createMock(AssetResolverInterface::class);
    $asset_resolver->method('getCssAssets')->willReturn([]);
    $asset_resolver->method('getJsAssets')->willReturn([[], []]);
    $command = new RefreshComponentAssetsCommand($component, $asset_resolver);
    // Test empty case.
    $render = $command->render();
    $this->assertEmpty($render['assets']);
    // Test mixed case.
    $asset_resolver = $this->createMock(AssetResolverInterface::class);
    $asset_resolver->method('getCssAssets')->willReturn([
      'css1' => [],
      'css2' => [],
      'core/css3' => [],
    ]);
    $asset_resolver->method('getJsAssets')->willReturn([NULL, [
      'jsfooter1' => [],
      'drupalSettings' => [],
    ],
    ]);
    $command = new RefreshComponentAssetsCommand($component, $asset_resolver);
    $render = $command->render();
    $this->assertEquals(['css1', 'css2', 'jsfooter1'], $render['assets']);
  }

}
