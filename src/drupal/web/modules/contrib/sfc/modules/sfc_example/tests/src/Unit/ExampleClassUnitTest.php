<?php

namespace Drupal\Tests\sfc_example\Unit;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\sfc_example\Plugin\SingleFileComponent\ExampleClass;
use Drupal\Tests\UnitTestCase;

/**
 * An example of how you could unit test some component methods.
 *
 * For now I only think that testing PHP methods makes sense, Twig templates
 * rely on too many hidden dependencies to be reasonable mocked and rendered.
 *
 * @coversDefaultClass \Drupal\sfc_example\Plugin\SingleFileComponent\ExampleClass
 *
 * @group sfc_example
 */
class ExampleClassUnitTest extends UnitTestCase {

  /**
   * Tests the ::prepareContext method.
   */
  public function testPrepareContext() {
    $file_system = $this->createMock(FileSystemInterface::class);
    $file_url_generator = $this->createMock(FileUrlGeneratorInterface::class);
    $current_user = $this->createMock(AccountProxyInterface::class);
    $current_user->method('getDisplayName')->willReturn('Default');
    $component = new ExampleClass([], 'example_class', [], FALSE, 'vfs:/', $file_system, $file_url_generator, $current_user);
    $context = [];
    $component->prepareContext($context);
    $this->assertEquals($context['name'], 'Default');
    $context = ['name' => 'Sam'];
    $component->prepareContext($context);
    $this->assertEquals($context['name'], 'Sam');
  }

}
