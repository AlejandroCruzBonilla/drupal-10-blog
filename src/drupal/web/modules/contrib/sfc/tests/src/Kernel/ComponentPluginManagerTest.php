<?php

namespace Drupal\Tests\sfc\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\sfc\Plugin\SingleFileComponent\SimpleComponent;

/**
 * Tests methods provided by the component plugin manager.
 *
 * @coversDefaultClass \Drupal\sfc\ComponentPluginManager
 *
 * @group sfc
 */
class ComponentPluginManagerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'sfc',
    'sfc_test',
  ];

  /**
   * Tests the ::createInstance method.
   */
  public function testCreateInstance() {
    /** @var \Drupal\Component\Plugin\PluginManagerInterface $manager */
    $manager = \Drupal::service('plugin.manager.single_file_component');
    // The "simple_test" plugin doesn't actually exist - this tests that
    // derived plugins which define an alt_id can be created using that alt_id.
    $component = $manager->createInstance('simple_test');
    $this->assertTrue($component instanceof SimpleComponent);
  }

}
