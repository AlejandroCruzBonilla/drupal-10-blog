<?php

namespace Drupal\Tests\sfc\Unit;

use Drupal\sfc\ComponentInterface;
use Drupal\sfc\ComponentNameHelper;
use Drupal\Tests\UnitTestCase;

/**
 * Tests methods provided by the component name helper.
 *
 * @coversDefaultClass \Drupal\sfc\ComponentNameHelper
 *
 * @group sfc
 */
class ComponentNameHelperTest extends UnitTestCase {

  /**
   * Tests the ::getLibraryName method.
   */
  public function testGetLibraryName() {
    $component = $this->createMock(ComponentInterface::class);
    $component->method('getId')->willReturn('test_component');
    $this->assertEquals('sfc/component.test_component', ComponentNameHelper::getLibraryName($component));
  }

  /**
   * Tests the ::isComponentLibrary method.
   */
  public function testIsComponentLibrary() {
    $this->assertFalse(ComponentNameHelper::isComponentLibrary('drupal/core'));
    $this->assertTrue(ComponentNameHelper::isComponentLibrary('sfc/component.foo'));
  }

  /**
   * Tests the ::getIdFromLibraryName method.
   */
  public function testGetIdFromLibraryName() {
    $this->assertEquals('test_component', ComponentNameHelper::getIdFromLibraryName('sfc/component.test_component'));
  }

  /**
   * Tests the ::isComponentTemplate method.
   */
  public function testIsComponentTemplate() {
    $this->assertFalse(ComponentNameHelper::isComponentTemplate('node-article.html.twig'));
    $this->assertTrue(ComponentNameHelper::isComponentTemplate('sfc--test-component.html.twig'));
  }

  /**
   * Tests the ::getTemplateName method.
   */
  public function testGetTemplateName() {
    $component = $this->createMock(ComponentInterface::class);
    $component->method('getId')->willReturn('test_component');
    $this->assertEquals('sfc--test-component', ComponentNameHelper::getTemplateName($component));
  }

  /**
   * Tests the ::getIdFromTemplateName method.
   */
  public function testGetIdFromTemplateName() {
    $this->assertEquals('test_component', ComponentNameHelper::getIdFromTemplateName('sfc--test-component.html.twig'));
  }

}
