<?php

namespace Drupal\Tests\sfc_example\Kernel;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Session\UserSession;
use Drupal\KernelTests\KernelTestBase;
use Drupal\sfc_example\Plugin\SingleFileComponent\ExampleClass;
use Drupal\Tests\sfc\Kernel\ComponentTestTrait;

/**
 * Tests the ExampleClass component.
 *
 * @group sfc_example
 */
class ExampleClassTest extends KernelTestBase {

  use ComponentTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'sfc',
    'sfc_example',
  ];

  /**
   * This test doesn't mock anything and is more like a light integration test.
   */
  public function testRenderIntegration() {
    $session = new UserSession([
      'name' => 'Default',
    ]);
    $proxy = \Drupal::service('current_user');
    $proxy->setAccount($session);
    \Drupal::currentUser()->setAccount($proxy);
    $this->assertEquals('<p class="example-class">Hello Default!</p>', $this->renderComponent('example_class', []));
    $this->assertEquals('<p class="example-class">Hello Sam!</p>', $this->renderComponent('example_class', [
      'name' => 'Sam',
    ]));
  }

  /**
   * This test uses mocking to test the component.
   */
  public function testRenderMock() {
    $file_system = $this->createMock(FileSystemInterface::class);
    $file_url_generator = $this->createMock(FileUrlGeneratorInterface::class);
    $current_user = $this->createMock(AccountProxyInterface::class);
    $current_user->method('getDisplayName')->willReturn('Default');
    $component = new ExampleClass([], 'example_class', [], FALSE, 'vfs:/', $file_system, $file_url_generator, $current_user);
    $this->assertEquals('<p class="example-class">Hello Default!</p>', $this->renderComponentObject($component, []));
    $this->assertEquals('<p class="example-class">Hello Sam!</p>', $this->renderComponentObject($component, [
      'name' => 'Sam',
    ]));
  }

}
