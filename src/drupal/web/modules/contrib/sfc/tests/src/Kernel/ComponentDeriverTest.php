<?php

namespace Drupal\Tests\sfc\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\sfc\Plugin\Block\ComponentBlock;
use Drupal\sfc\Plugin\Layout\ComponentLayout;

/**
 * Tests the functionality of component derivers.
 *
 * @group sfc
 */
class ComponentDeriverTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'sfc',
    'sfc_test',
    'layout_discovery',
  ];

  /**
   * Tests that component derivers find annotated components.
   */
  public function testDerivers() {
    $block = \Drupal::service('plugin.manager.block')->createInstance('single_file_component_block:say_hello');
    $this->assertTrue($block instanceof ComponentBlock);
    $layout = \Drupal::service('plugin.manager.core.layout')->createInstance('single_file_component_layout:two_column_flexible');
    $this->assertTrue($layout instanceof ComponentLayout);
    $block = \Drupal::service('plugin.manager.block')->createInstance('single_file_component_invalid_block:say_hello');
    $this->assertFalse($block instanceof ComponentLayout);
  }

}
