<?php

namespace Drupal\Tests\sfc\Kernel;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the "sfc" element.
 *
 * @coversDefaultClass \Drupal\sfc\Element\ComponentElement
 *
 * @group sfc
 *
 * @codeCoverageIgnore
 */
class ComponentElementTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'sfc',
    'sfc_test',
  ];

  /**
   * Tests element rendering conditions.
   */
  public function testElement() {
    $build = [
      '#type' => 'sfc',
      '#component_id' => 'simple_test',
    ];
    $this->assertStringContainsString('Default value', (string) \Drupal::service('renderer')->renderPlain($build));
    $build = [
      '#type' => 'sfc',
      '#component_id' => 'simple_test',
      '#context' => [
        'message' => 'Hello world',
      ],
    ];
    $this->assertStringContainsString('Hello world', (string) \Drupal::service('renderer')->renderPlain($build));
    $this->expectException(PluginException::class);
    $build = [
      '#type' => 'sfc',
      '#component_id' => 'error',
    ];
    \Drupal::service('renderer')->renderPlain($build);
  }

}
