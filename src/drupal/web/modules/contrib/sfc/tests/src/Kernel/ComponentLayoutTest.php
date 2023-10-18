<?php

namespace Drupal\Tests\sfc\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests methods provided by the component layout.
 *
 * @coversDefaultClass \Drupal\sfc\Plugin\Layout\ComponentLayout
 *
 * @group sfc
 */
class ComponentLayoutTest extends KernelTestBase {

  use ComponentTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'sfc',
    'sfc_test',
    'layout_discovery',
  ];

  /**
   * Tests the ::build method.
   */
  public function testBuild() {
    /** @var \Drupal\sfc\Plugin\Layout\ComponentLayout $layout */
    $layout = \Drupal::service('plugin.manager.core.layout')->createInstance('single_file_component_layout:two_column_flexible');
    $renderer = \Drupal::service('renderer');
    $build = $layout->build([]);
    $render = $renderer->renderPlain($build);
    $this->assertEquals('<div class="two-column sizing-50-50">
  <div class="left">
    
  </div>
  <div class="right">
    
  </div>
</div>', $render);
    $build = $layout->build([
      'right' => [
        [
          '#markup' => 'Right side',
        ],
        '#attributes' => [
          'class' => ['right-class'],
        ],
      ],
      'left' => [
        [
          '#markup' => 'Left side',
        ],
      ],
    ]);
    $build['#attributes']['class'][] = 'layout-class';
    $render = $renderer->renderPlain($build);
    $this->assertEquals('<div class="layout-class two-column sizing-50-50">
  <div class="left">
    Left side
  </div>
  <div class="right-class right">
    Right side
  </div>
</div>', $render);
  }

}
