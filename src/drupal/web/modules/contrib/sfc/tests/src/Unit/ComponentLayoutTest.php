<?php

namespace Drupal\Tests\sfc\Unit;

use Drupal\Core\Form\FormState;
use Drupal\sfc\ComponentBase;
use Drupal\sfc\ComponentInterface;
use Drupal\sfc\Plugin\Layout\ComponentLayout;
use Drupal\Tests\UnitTestCase;

/**
 * Tests methods provided by the component layout.
 *
 * @coversDefaultClass \Drupal\sfc\Plugin\Layout\ComponentLayout
 *
 * @group sfc
 */
class ComponentLayoutTest extends UnitTestCase {

  /**
   * Tests the ::buildConfigurationForm method.
   */
  public function testBuildConfigurationForm() {
    $component = $this->createMock(ComponentInterface::class);
    $block = new ComponentLayout([
      'component_context' => [
        'foo' => 'bar',
      ],
    ], 'test', [
      'provider' => 'sfc',
    ], $component);
    $form_state = new FormState();
    $form = $block->buildConfigurationForm([], $form_state);
    $this->assertArrayHasKey('component_context', $form);
    $this->assertEquals(['foo' => 'bar'], $form['component_context']['#component_context']);
  }

  /**
   * Tests the ::validateConfigurationForm method.
   */
  public function testValidateConfigurationForm() {
    $component = $this->createMock(ComponentBase::class);
    $component->expects($this->once())->method('validateContextForm');
    $block = new ComponentLayout([], 'test', [
      'provider' => 'sfc',
    ], $component);
    $form = ['component_context' => ['#parents' => []]];
    $block->validateConfigurationForm($form, new FormState());
  }

  /**
   * Tests the ::submitConfigurationForm method.
   */
  public function testSubmitConfigurationForm() {
    $component = $this->createMock(ComponentInterface::class);
    $block = new ComponentLayout([], 'test', [
      'provider' => 'sfc',
    ], $component);
    $form = [];
    $values = $block->submitConfigurationForm($form, new FormState());
    $this->assertEmpty($values);

    $component = $this->createMock(ComponentBase::class);
    $component->expects($this->once())->method('submitContextForm');
    $block = new ComponentLayout([], 'test', [
      'provider' => 'sfc',
    ], $component);
    $form = ['#parents' => [], 'component_context' => ['#parents' => []]];
    $block->submitConfigurationForm($form, new FormState());
  }

}
