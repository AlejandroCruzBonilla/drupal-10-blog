<?php

namespace Drupal\Tests\sfc\Unit;

use Drupal\Core\Form\FormState;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\sfc\ComponentBase;
use Drupal\sfc\ComponentInterface;
use Drupal\sfc\Plugin\Block\ComponentBlock;
use Drupal\Tests\UnitTestCase;

/**
 * Tests methods provided by the component block.
 *
 * @coversDefaultClass \Drupal\sfc\Plugin\Block\ComponentBlock
 *
 * @group sfc
 */
class ComponentBlockTest extends UnitTestCase {

  /**
   * Tests the ::blockForm method.
   */
  public function testBlockForm() {
    $component = $this->createMock(ComponentInterface::class);
    $block = new ComponentBlock([
      'component_context' => [
        'foo' => 'bar',
      ],
    ], 'test', [
      'provider' => 'sfc',
    ], $component);
    $form_state = new FormState();
    $form = $block->blockForm([], $form_state);
    $this->assertArrayHasKey('component_context', $form);
    $this->assertEquals(['foo' => 'bar'], $form['component_context']['#component_context']);
  }

  /**
   * Tests the ::processComponentForm method.
   */
  public function testProcessComponentForm() {
    $component = $this->createMock(ComponentInterface::class);
    $block = new ComponentBlock([], 'test', [
      'provider' => 'sfc',
    ], $component);
    $translation = $this->createMock(TranslationInterface::class);
    $block->setStringTranslation($translation);
    $form_state = new FormState();
    $form = $block->blockForm([], $form_state);
    $element = $block->processComponentForm($form['component_context'], $form_state, $form);
    $this->assertArrayHasKey('empty', $element);
    $component = $this->createMock(ComponentBase::class);
    $component->method('buildContextForm')->willReturn(['foo' => []]);
    $block = new ComponentBlock([], 'test', [
      'provider' => 'sfc',
    ], $component);
    $form = $block->blockForm([], $form_state);
    $element = $block->processComponentForm($form['component_context'], $form_state, $form);
    $this->assertArrayNotHasKey('empty', $element);
    $this->assertArrayHasKey('foo', $element);
  }

  /**
   * Tests the ::blockValidate method.
   */
  public function testBlockValidate() {
    $component = $this->createMock(ComponentBase::class);
    $component->expects($this->once())->method('validateContextForm');
    $block = new ComponentBlock([], 'test', [
      'provider' => 'sfc',
    ], $component);
    $block->blockValidate(['component_context' => ['#parents' => []]], new FormState());
  }

  /**
   * Tests the ::blockSubmit method.
   */
  public function testBlockSubmit() {
    $component = $this->createMock(ComponentInterface::class);
    $block = new ComponentBlock([], 'test', [
      'provider' => 'sfc',
    ], $component);
    $values = $block->blockSubmit([], new FormState());
    $this->assertEmpty($values);

    $component = $this->createMock(ComponentBase::class);
    $component->expects($this->exactly(2))->method('submitContextForm');
    $block = new ComponentBlock([], 'test', [
      'provider' => 'sfc',
    ], $component);
    $block->blockSubmit([
      '#parents' => [],
      'component_context' => [
        '#parents' => [],
      ],
    ], new FormState());

    $form_state = new FormState();
    $form_state->setTemporaryValue('component_form_parents', [
      'sub_form',
      'component_context',
    ]);
    $block->blockSubmit([
      '#parents' => [],
      'sub_form' => [
        'component_context' => [
          '#parents' => [],
        ],
      ],
    ], $form_state);
  }

}
