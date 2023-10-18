<?php

namespace Drupal\Tests\sfc\Unit;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Template\Attribute;
use Drupal\sfc\LayoutComponentBase;
use Drupal\Tests\UnitTestCase;

/**
 * Tests methods provided by the layout component base class.
 *
 * @coversDefaultClass \Drupal\sfc\LayoutComponentBase
 *
 * @group sfc
 */
class LayoutComponentBaseTest extends UnitTestCase {

  /**
   * Tests the ::prepareContext method.
   */
  public function testPrepareContext() {
    $file_system = $this->createMock(FileSystemInterface::class);
    $file_url_generator = $this->createMock(FileUrlGeneratorInterface::class);
    $component = new LayoutComponentBase([], 'test_component', [
      'layout' => [
        'regions' => [
          'foo' => [],
        ],
      ],
    ], FALSE, 'vfs:/', $file_system, $file_url_generator);
    $context = [];
    $component->prepareContext($context);
    $this->assertTrue($context['attributes'] instanceof Attribute);
    $this->assertTrue($context['region_attributes']['foo'] instanceof Attribute);
    $this->assertArrayHasKey('foo', $context['content']);
    $context = [
      'attributes' => [
        'class' => ['foo'],
      ],
      'region_attributes' => [
        'foo' => [
          'class' => ['foo'],
        ],
      ],
    ];
    $component->prepareContext($context);
    $this->assertTrue($context['attributes'] instanceof Attribute);
    $this->assertEquals('foo', $context['attributes']->getClass());
    $this->assertTrue($context['region_attributes']['foo'] instanceof Attribute);
    $this->assertEquals('foo', $context['region_attributes']['foo']->getClass());
  }

}
