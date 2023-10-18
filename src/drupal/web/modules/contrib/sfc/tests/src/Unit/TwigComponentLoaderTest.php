<?php

namespace Drupal\Tests\sfc\Unit;

use Drupal\sfc\ComponentBase;
use Drupal\sfc\ComponentPluginManager;
use Drupal\sfc\TwigComponentLoader;
use Drupal\Tests\UnitTestCase;
use Twig\Error\LoaderError;

/**
 * Tests methods provided by the twig component loader.
 *
 * @coversDefaultClass \Drupal\sfc\TwigComponentLoader
 *
 * @group sfc
 */
class TwigComponentLoaderTest extends UnitTestCase {

  /**
   * The loader.
   *
   * @var \Drupal\sfc\TwigComponentLoader
   */
  protected $loader;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $manager = $this->createMock(ComponentPluginManager::class);
    $component = $this->createMock(ComponentBase::class);
    $component->method('getTemplate')->willReturn('test');
    $manager->method('createInstance')->willReturn($component);
    $manager->method('getDefinitions')->willReturn([]);
    $this->loader = new TwigComponentLoader($manager);
  }

  /**
   * Tests the ::getSource method.
   */
  public function testGetSource() {
    $this->assertEquals('test', $this->loader->getSource('sfc--test'));
    $this->assertEquals(FALSE, $this->loader->getSource('test'));
  }

  /**
   * Tests the ::exists method.
   */
  public function testExists() {
    $this->assertTrue($this->loader->exists('sfc--test'));
    $this->assertFalse($this->loader->exists('test'));
  }

  /**
   * Tests the ::getCacheKey method.
   */
  public function testGetCacheKey() {
    $this->assertEquals('sfc--test:test', $this->loader->getCacheKey('sfc--test'));
    $this->expectException(LoaderError::class);
    // @codeCoverageIgnoreStart
    $this->loader->getCacheKey('test');
  }

  // @codeCoverageIgnoreEnd

  /**
   * Tests the ::isFresh method.
   */
  public function testIsFresh() {
    $this->assertTrue($this->loader->isFresh('sfc--test', 0));
    $this->expectException(LoaderError::class);
    // @codeCoverageIgnoreStart
    $this->loader->isFresh('test', 0);
  }

  // @codeCoverageIgnoreEnd
}
