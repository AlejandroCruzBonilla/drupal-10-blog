<?php

namespace Drupal\Tests\sfc\Unit;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Form\FormState;
use Drupal\sfc\ComponentBase;
use Drupal\Tests\UnitTestCase;
use org\bovigo\vfs\vfsStream;

/**
 * Tests methods provided by the base component.
 *
 * @coversDefaultClass \Drupal\sfc\ComponentBase
 *
 * @group sfc
 */
class ComponentBaseTest extends UnitTestCase {

  /**
   * Tests the ::getTemplate method.
   */
  public function testGetTemplate() {
    $file_system = $this->createMock(FileSystemInterface::class);
    $file_url_generator = $this->createMock(FileUrlGeneratorInterface::class);
    $component = new TestComponent([], 'test_component', [], FALSE, 'vfs:/', $file_system, $file_url_generator);
    // Test that the template automatically adds functions.
    $component->setData('TEMPLATE', 'Hello {{ name }}!');
    $this->assertStringContainsString("{{ sfc_prepare_context('test_component') }}", $component->getTemplate());
    $this->assertStringContainsString("{{ attach_library('sfc/component.test_component') }}", $component->getTemplate());
    $this->assertStringContainsString('Hello {{ name }}!', $component->getTemplate());
    $this->assertStringNotContainsString('<!-- SFC debug -->', $component->getTemplate());
    // Test that debug markup is present.
    $component = new TestComponent([], 'test_component', [], TRUE, 'vfs:/', $file_system, $file_url_generator);
    $component->setData('TEMPLATE', 'Hello {{ name }}!');
    $this->assertStringContainsString('<!-- SFC debug -->', $component->getTemplate());
  }

  /**
   * Tests the ::getLibrary method.
   */
  public function testGetLibrary() {
    $file_system = $this->createMock(FileSystemInterface::class);
    $file_url_generator = $this->createMock(FileUrlGeneratorInterface::class);
    $file_url_generator->method('generateString')->willReturnCallback(function ($uri) {
      return $uri;
    });
    $component = new TestComponent([], 'test_component', [], FALSE, 'vfs:/', $file_system, $file_url_generator);
    $this->assertEquals([], $component->getLibrary());
    // Test that DEPENDENCIES and LIBRARY are properly merged.
    $component->setData('LIBRARY', ['dependencies' => ['foo', 'bar']]);
    $this->assertEquals(['dependencies' => ['foo', 'bar']], $component->getLibrary());
    $component->setData('DEPENDENCIES', ['foo', 'baz']);
    $this->assertEquals(['dependencies' => ['foo', 'bar', 'baz']], $component->getLibrary());
    $component = new TestComponent([], 'test_component', [], FALSE, 'vfs:/', $file_system, $file_url_generator);
    $this->assertEquals([], $component->getLibrary());
    // Test that DEPENDENCIES alone works as expected.
    $component->setData('DEPENDENCIES', ['foo', 'baz']);
    $this->assertEquals(['dependencies' => ['foo', 'baz']], $component->getLibrary());
    $component = new TestComponent([], 'test_component', [], FALSE, 'vfs:/', $file_system, $file_url_generator);
    $component->setData('CSS', 'foo');
    $this->assertEquals([
      'css' => [
        'theme' => [
          'vfs://sfc/components/test_component/test_component.css' => [],
        ],
      ],
    ], $component->getLibrary());
    $component = new TestComponent([], 'test_component', [], FALSE, 'vfs:/', $file_system, $file_url_generator);
    $component->setData('JS', 'foo');
    $this->assertEquals([
      'js' => [
        'vfs://sfc/components/test_component/test_component.js' => [],
      ],
    ], $component->getLibrary());
    $component = new TestComponent([], 'test_component', [], FALSE, 'vfs:/', $file_system, $file_url_generator);
    // Tests that when attachments are used, dependencies are added.
    $component->setData('ATTACH', 'foo');
    $this->assertEquals([
      'js' => [
        'vfs://sfc/components/test_component/test_component.js' => [],
      ],
      'dependencies' => [
        'core/drupal',
        'core/drupalSettings',
        'core/jquery',
        'core/once',
      ],
    ], $component->getLibrary());
    $component->setData('VANILLA_JS', TRUE);
    $this->assertEquals([
      'js' => [
        'vfs://sfc/components/test_component/test_component.js' => [],
      ],
      'dependencies' => [
        'core/drupal',
        'core/drupalSettings',
        'core/once',
      ],
    ], $component->getLibrary());
  }

  /**
   * Tests the ::writeAssets method.
   */
  public function testWriteAssets() {
    vfsStream::setup('sfc');

    $js_file = 'vfs://sfc/components/test_component/test_component.js';
    $css_file = 'vfs://sfc/components/test_component/test_component.css';
    $file_system = $this->createMock(FileSystemInterface::class);
    $file_url_generator = $this->createMock(FileUrlGeneratorInterface::class);
    // We need these methods to be called to get full test coverage.
    $file_system->method('mkdir')->willReturnCallback(function () {
      if (!is_dir('vfs://sfc/components/test_component')) {
        mkdir('vfs://sfc/components/test_component', 0444, TRUE);
      }
      return;
    });
    $file_system->method('chmod')->willReturnCallback(function () {
      chmod('vfs://sfc/components', 0777);
      chmod('vfs://sfc/components/test_component', 0777);
      return;
    });
    $component = new TestComponent([], 'test_component', [
      'provider' => 'sfc',
    ], FALSE, 'vfs:/', $file_system, $file_url_generator);
    $component->writeAssets();
    $this->assertFileDoesNotExist($js_file);
    $this->assertFileDoesNotExist($css_file);
    $component->setData('CSS', '.foo { color: pink; } .bar { background-image: url(../foo); } .baz { background-image: url(/foo); } .quz { background-image: url(http://google.com); .bar { background-image: url(foo); }');
    $component->writeAssets();
    $this->assertFileExists($css_file);
    $this->assertStringEqualsFile($css_file, '.foo { color: pink; } .bar { background-image: url(' . realpath(__DIR__ . '/../../..') . '/../foo); } .baz { background-image: url(/foo); } .quz { background-image: url(http://google.com); .bar { background-image: url(' . realpath(__DIR__ . '/../../..') . '/foo); }');
    $component->setData('JS', 'bar');
    $component->writeAssets();
    $this->assertFileExists($js_file);
    $this->assertStringEqualsFile($js_file, "bar\n");
    $component->setData('ATTACH', 'baz');
    $component->setData('SELECTOR', '.selector');
    $component->setData('JS', NULL);
    $component->writeAssets();
    $this->assertFileExists($js_file);
    $contents = file_get_contents($js_file);
    $this->assertStringContainsString('baz', $contents);
    // Lots of boilerplate JS is added when attachments are used.
    $this->assertStringContainsString('Drupal.behaviors.sfc_test_component = {', $contents);
    $this->assertStringContainsString('$(".selector", context)', $contents);
    $this->assertStringContainsString('attach: function', $contents);
    $this->assertStringNotContainsString("once('sfcDetach').each", $contents);
    $component->setData('DETACH', 'qux');
    $component->writeAssets();
    $contents = file_get_contents($js_file);
    $this->assertStringContainsString('qux', $contents);
    $this->assertStringContainsString('attach: function', $contents);
    $this->assertStringContainsString("once.remove('sfcAttach', element)", $contents);
    // Test vanilla JS.
    $component->setData('VANILLA_JS', TRUE);
    $component->writeAssets();
    $this->assertFileExists($js_file);
    $contents = file_get_contents($js_file);
    $this->assertStringContainsString('Drupal.behaviors.sfc_test_component = {', $contents);
    $this->assertStringContainsString('attach: function', $contents);
    $this->assertStringContainsString('detach: function', $contents);
    $this->assertStringContainsString('once(\'sfcAttach\', ".selector", context)', $contents);
    $this->assertStringContainsString('once(\'sfcDetach\', ".selector", context)', $contents);
  }

  /**
   * Tests the ::shouldWriteAssets method.
   */
  public function testShouldWriteAssets() {
    vfsStream::setup('sfc');

    $js_file = 'vfs://sfc/components/test_component/test_component.js';
    $css_file = 'vfs://sfc/components/test_component/test_component.css';
    $file_system = $this->createMock(FileSystemInterface::class);
    $file_url_generator = $this->createMock(FileUrlGeneratorInterface::class);
    $component = new WriteTestConstComponent([], 'test_component', [], FALSE, 'vfs:/', $file_system, $file_url_generator);
    mkdir('vfs://sfc/components/test_component', 0777, TRUE);
    // If the file doesn't exist.
    $this->assertTrue($component->shouldWriteAssets());
    // If only one of the asset files exists.
    touch($css_file);
    $this->assertTrue($component->shouldWriteAssets());
    // If both asset files exist.
    touch($js_file);
    $this->assertFalse($component->shouldWriteAssets());
    // If one asset file is out of date.
    unlink($js_file);
    unlink($css_file);
    touch($js_file);
    touch($css_file, 1);
    $this->assertTrue($component->shouldWriteAssets());
    // If the other asset file is out of date.
    unlink($js_file);
    unlink($css_file);
    touch($js_file, 1);
    touch($css_file);
    $this->assertTrue($component->shouldWriteAssets());
    // If no assets are defined.
    $component = new EmptyTestComponent([], 'test_component', [], FALSE, 'vfs:/', $file_system, $file_url_generator);
    $this->assertFalse($component->shouldWriteAssets());
  }

  /**
   * Tests the ::buildContextForm method.
   */
  public function testBuildContextForm() {
    $file_system = $this->createMock(FileSystemInterface::class);
    $file_url_generator = $this->createMock(FileUrlGeneratorInterface::class);
    $component = new TestComponent([], 'test_component', [], FALSE, 'vfs:/', $file_system, $file_url_generator);
    $this->assertEquals(['foo'], $component->buildContextForm(['foo'], new FormState()));
  }

  /**
   * Ensures that defining constants works as expected.
   */
  public function testConstants() {
    vfsStream::setup('sfc');

    $file_system = $this->createMock(FileSystemInterface::class);
    $file_url_generator = $this->createMock(FileUrlGeneratorInterface::class);
    $file_url_generator->method('generateString')->willReturnCallback(function ($uri) {
      return $uri;
    });
    $component = new TestConstComponent([], 'test_component', [
      'provider' => 'sfc',
    ], FALSE, 'vfs:/', $file_system, $file_url_generator);
    $this->assertStringContainsString('template', $component->getTemplate());
    $this->assertEquals([
      'js' => [
        'public://sfc/components/test_component/test_component.js' => [],
        realpath(__DIR__ . '/../../..') . '/relative.js' => [],
      ],
      'css' => [
        'theme' => [
          'public://sfc/components/test_component/test_component.css' => [],
          '/css.css' => [],
          realpath(__DIR__ . '/../../..') . '/relative.css' => [],
        ],
      ],
      'dependencies' => [
        'dependency',
        'core/drupal',
        'core/drupalSettings',
        'core/jquery',
        'core/once',
      ],
    ], $component->getLibrary());

    mkdir('vfs://sfc/components/test_component', 0777, TRUE);
    $file_system = $this->createMock(FileSystemInterface::class);
    $file_url_generator = $this->createMock(FileUrlGeneratorInterface::class);
    $component = new WriteTestConstComponent([], 'test_component', [
      'provider' => 'sfc',
    ], FALSE, 'vfs:/', $file_system, $file_url_generator);
    $component->writeAssets();
    $this->assertFileExists('vfs://sfc/components/test_component/test_component.js');
    $this->assertFileExists('vfs://sfc/components/test_component/test_component.css');
  }

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  protected function tearDown(): void {
    vfsStream::setup('sfc');
    if (is_file('vfs://sfc/components/test_component/test_component.js')) {
      unlink('vfs://sfc/components/test_component/test_component.js');
    }
    if (is_file('vfs://sfc/components/test_component/test_component.css')) {
      unlink('vfs://sfc/components/test_component/test_component.css');
    }
    if (is_dir('vfs://sfc/components/test_component')) {
      rmdir('vfs://sfc/components/test_component');
      rmdir('vfs://sfc/components');
    }
  }

}

// phpcs:disable
// @codeCoverageIgnoreStart

class EmptyTestComponent extends ComponentBase {}

class TestConstComponent extends ComponentBase {

  const TEMPLATE = 'template';

  const CSS = 'css';

  const JS = 'js';

  const SELECTOR = 'selector';

  const ATTACH = 'attach';

  const DETACH = 'detach';

  const DEPENDENCIES = [
    'dependency',
  ];

  const LIBRARY = [
    'css' => [
      'theme' => [
        '/css.css' => [],
        'relative.css' => [],
      ],
    ],
    'js' => [
      'relative.js' => [],
    ],
  ];

}

class WriteTestConstComponent extends TestConstComponent {

  protected function getAssetPath() {
    $path = parent::getAssetPath();
    return str_replace('public://', 'vfs://', $path);
  }

}

class TestComponent extends ComponentBase {

  protected $data = [];

  public function setData($name, $data) {
    $this->data[$name] = $data;
  }

  public function getData($name) {
    return isset($this->data[$name]) ? $this->data[$name] : NULL;
  }

  protected function getAssetPath() {
    $path = parent::getAssetPath();
    return str_replace('public://', 'vfs://', $path);
  }

  protected function getAttachmentData() {
    return [
      'selector' => $this->getData('SELECTOR'),
      'attach' => $this->getData('ATTACH'),
      'detach' => $this->getData('DETACH'),
      'vanilla_js' => $this->getData('VANILLA_JS')
    ];
  }

  protected function getCss() {
    return $this->replaceCssPaths($this->getData('CSS'));
  }

  protected function getJs() {
    return $this->getData('JS');
  }

  protected function getTemplateData() {
    return $this->getData('TEMPLATE');
  }

  protected function getLibraryData() {
    return $this->getData('LIBRARY');
  }

  protected function getDependencies() {
    return $this->getData('DEPENDENCIES');
  }

  protected function hasAttachments() {
    return $this->getData('ATTACH') || $this->getData('DETACH');
  }

  protected function hasJs() {
    return (bool) $this->getData('JS');
  }

  protected function hasCss() {
    return (bool) $this->getData('CSS');
  }

  protected function hasLibraryData() {
    return (bool) $this->getData('LIBRARY');
  }

  protected function hasDependencies() {
    return (bool) $this->getData('DEPENDENCIES');
  }

}

namespace Drupal\sfc;

if (!function_exists('Drupal\sfc\file_url_transform_relative')) {
  function file_url_transform_relative($file_url) {
    return $file_url;
  }
}

if (!function_exists('Drupal\sfc\file_create_url')) {
  function file_create_url($uri) {
    return $uri;
  }
}

// phpcs:enable
// @codeCoverageIgnoreEnd
