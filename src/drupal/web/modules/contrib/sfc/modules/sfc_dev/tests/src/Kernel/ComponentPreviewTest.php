<?php

namespace Drupal\Tests\sfc_dev\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\sfc\Kernel\ComponentTestTrait;

/**
 * Tests the component preview component.
 *
 * @coversDefaultClass \Drupal\sfc_dev\Plugin\SingleFileComponent\ComponentPreview
 *
 * @group sfc_dev
 */
class ComponentPreviewTest extends KernelTestBase {

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
    $output = $this->renderComponent('sfc_dev_component_preview', []);
    $this->assertStringContainsString('Select a component to get started', $output);
    $output = $this->renderComponent('sfc_dev_component_preview', [
      'component' => $component,
    ]);
    $this->assertStringContainsString('Preview for say_hello', $output);
    $this->assertStringContainsString('Hello !', $output);
  }

}
