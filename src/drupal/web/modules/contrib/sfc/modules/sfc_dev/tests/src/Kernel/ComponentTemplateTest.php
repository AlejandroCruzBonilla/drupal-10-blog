<?php

namespace Drupal\Tests\sfc_dev\Kernel;

use Drupal\Component\Utility\Html;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\sfc\Kernel\ComponentTestTrait;

/**
 * Tests the component template component.
 *
 * @coversDefaultClass \Drupal\sfc_dev\Plugin\SingleFileComponent\ComponentTemplate
 *
 * @group sfc_dev
 */
class ComponentTemplateTest extends KernelTestBase {

  use ComponentTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'sfc',
    'sfc_dev',
    'sfc_test',
    'system',
  ];

  /**
   * Tests that the component renders as expected.
   */
  public function testComponent() {
    $component = \Drupal::service('plugin.manager.single_file_component')->createInstance('say_hello');
    $output = $this->renderComponent('sfc_dev_component_template', []);
    $template = Html::escape('<p class="say-hello">Hello {{ name }}!</p>');
    $this->assertStringNotContainsString($template, $output);
    $output = $this->renderComponent('sfc_dev_component_template', [
      'component' => $component,
    ]);
    $this->assertStringContainsString($template, $output);
  }

}
