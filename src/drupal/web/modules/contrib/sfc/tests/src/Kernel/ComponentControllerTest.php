<?php

namespace Drupal\Tests\sfc\Kernel;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\KernelTests\KernelTestBase;
use Drupal\sfc\Controller\ComponentController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests methods provided by the component controller.
 *
 * @coversDefaultClass \Drupal\sfc\Controller\ComponentController
 *
 * @group sfc
 *
 * @codeCoverageIgnore
 */
class ComponentControllerTest extends KernelTestBase {

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
    $controller = ComponentController::create($this->container);
    $request = new Request();
    $request->attributes->set('name', 'Sam');
    $build = $controller->build('homepage', $request);
    $this->assertStringContainsString('Welcome to the homepage!', (string) \Drupal::service('renderer')->renderPlain($build));
    $build = $controller->build('hello_page', $request);
    $this->assertStringContainsString('Hello Sam!', (string) \Drupal::service('renderer')->renderPlain($build));

    $this->expectException(PluginException::class);
    $build = $controller->build('error', $request);
    \Drupal::service('renderer')->renderPlain($build);
  }

}
