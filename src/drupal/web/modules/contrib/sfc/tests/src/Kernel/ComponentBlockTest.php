<?php

namespace Drupal\Tests\sfc\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests methods provided by the component block.
 *
 * @coversDefaultClass \Drupal\sfc\Plugin\Block\ComponentBlock
 *
 * @group sfc
 */
class ComponentBlockTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'sfc',
    'sfc_test',
  ];

  /**
   * Tests the ::build method.
   */
  public function testBuild() {
    /** @var \Drupal\sfc\Plugin\Block\ComponentBlock $block */
    $block = \Drupal::service('plugin.manager.block')->createInstance('single_file_component_block:say_hello');
    $renderer = \Drupal::service('renderer');
    $build = $block->build();
    $render = $renderer->renderPlain($build);
    $this->assertEquals('<p class="say-hello">Hello !</p>', $render);
    $block->setConfiguration([
      'component_context' => [
        'name' => 'Sam',
      ],
    ]);
    $build = $block->build();
    $render = $renderer->renderPlain($build);
    $this->assertEquals('<p class="say-hello">Hello Sam!</p>', $render);
  }

}
