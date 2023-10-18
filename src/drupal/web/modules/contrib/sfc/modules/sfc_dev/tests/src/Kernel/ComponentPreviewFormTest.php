<?php

namespace Drupal\Tests\sfc_dev\Kernel;

use Drupal\Core\Asset\AssetResolver;
use Drupal\Core\Form\FormState;
use Drupal\KernelTests\KernelTestBase;
use Drupal\sfc\ComponentBase;
use Drupal\sfc\ComponentInterface;
use Drupal\sfc_dev\Form\ComponentPreviewForm;

/**
 * Tests the component preview form.
 *
 * @coversDefaultClass \Drupal\sfc_dev\Form\ComponentPreviewForm
 *
 * @group sfc_dev
 */
class ComponentPreviewFormTest extends KernelTestBase {

  /**
   * Tests the ::buildForm method.
   */
  public function testBuildForm() {
    $resolver = $this->createMock(AssetResolver::class);
    $form_object = new ComponentPreviewForm($resolver);
    $component = $this->createMock(ComponentInterface::class);
    $form = $form_object->buildForm([], new FormState(), $component);
    $this->assertNotEmpty($form);
  }

  /**
   * Tests the ::validateForm method.
   */
  public function testValidateForm() {
    $resolver = $this->createMock(AssetResolver::class);
    $form_object = new ComponentPreviewForm($resolver);
    $component = $this->createMock(ComponentBase::class);
    $form_state = new FormState();
    $form = [
      'twig' => ['#parents' => []],
      '#component' => $component,
      'component_context' => ['#parents' => []],
      '#parents' => [],
    ];
    $form_object->validateForm($form, $form_state);
    $this->assertEmpty($form_state->getErrors());
    $form_state->clearErrors();
    $form_state->setValue('mode', 'twig');
    $form_state->setValue('twig', '#foo');
    $form_object->validateForm($form, $form_state);
    $this->assertNotEmpty($form_state->getErrors());
    $form_state->clearErrors();
    $form_state->setValue('mode', 'form');
    $component->expects($this->once())->method('validateContextForm');
    $form_object->validateForm($form, $form_state);
  }

  /**
   * Tests the ::ajaxReload method.
   */
  public function testAjaxReload() {
    $resolver = $this->createMock(AssetResolver::class);
    $form_object = new ComponentPreviewForm($resolver);
    $component = $this->createMock(ComponentInterface::class);
    $component->expects($this->any())->method('getPluginId')->willReturn('test');
    $form = [
      '#component' => $component,
    ];
    $form_state = new FormState();
    $form_state->set('component_build', [
      'foo' => [],
    ]);
    $response = $form_object->ajaxReload($form, $form_state);
    $this->assertNotEmpty($response->getCommands());
  }

  /**
   * Tests the ::submitForm method.
   */
  public function testSubmitForm() {
    $resolver = $this->createMock(AssetResolver::class);
    $form_object = new ComponentPreviewForm($resolver);
    $component = $this->createMock(ComponentInterface::class);
    $component->expects($this->once())->method('getId')->willReturn('test');
    $form = [
      '#component' => $component,
    ];
    $form_state = new FormState();
    $form_state->setValue('twig', '{{ test }}');
    $form_object->submitForm($form, $form_state);
    $this->assertEquals($form_state->get('component_build')['#template'], '{{ test }}');
    $form_state->setValue('twig', '{{ test }}');
    $form_state->setValue('mode', 'form');
    $form_object->submitForm($form, $form_state);
    $this->assertEquals($form_state->get('component_build')['#template'], '{% include "sfc--test" %}');
  }

}
