<?php

namespace Drupal\sfc;

use Drupal\Core\Render\Element;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\SubformStateInterface;

/**
 * Contains methods that help with rendering components and their forms.
 */
trait ComponentConsumerTrait {

  /**
   * Builds a component for display.
   *
   * @param \Drupal\sfc\ComponentInterface $component
   *   A component.
   * @param array $component_context
   *   The component context.
   *
   * @return array
   *   A render array.
   */
  protected function componentBuild(ComponentInterface $component, array $component_context) {
    return [
      '#type' => 'inline_template',
      '#template' => '{% include "' . addcslashes(ComponentNameHelper::getTemplateName($component), "'") . '" %}',
      '#context' => $component_context,
      '#pre_render' => [
        [RenderCallbacks::class, 'preRenderComponent'],
      ],
    ];
  }

  /**
   * Adds attributes as context to the component template.
   *
   * @param array $element
   *   The element.
   *
   * @return array
   *   The modified element.
   */
  public static function preRenderComponent(array $element) {
    return RenderCallbacks::preRenderComponent($element);
  }

  /**
   * Builds a component's form.
   *
   * @param \Drupal\sfc\ComponentInterface $component
   *   A component.
   * @param array $component_context
   *   The component context, for setting default values.
   * @param array $form
   *   The form.
   *
   * @return array
   *   The complete form array.
   */
  protected function componentBuildForm(ComponentInterface $component, array $component_context, array $form) {
    $form['component_context'] = [
      '#type' => 'container',
      '#tree' => TRUE,
      '#component' => $component,
      '#component_context' => $component_context,
      '#process' => [[$this, 'processComponentForm']],
    ];
    return $form;
  }

  /**
   * Process callback to insert a component form.
   *
   * @param array $element
   *   The containing element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param array $form
   *   The form.
   *
   * @return array
   *   The containing element, with the inserted component form.
   */
  public function processComponentForm(array $element, FormStateInterface $form_state, array $form) {
    if ($element['#component'] instanceof ComponentFormInterface) {
      $sub_form_state = SubformState::createForSubform($element, $form, $form_state);
      $element = $element['#component']->buildContextForm($element, $sub_form_state, $element['#component_context']);
    }
    if (empty(Element::getVisibleChildren($element))) {
      $element['empty'] = [
        '#markup' => $this->t('This component does not provide a form.'),
      ];
    }
    return $element;
  }

  /**
   * Validates a component form.
   *
   * @param \Drupal\sfc\ComponentInterface $component
   *   A component.
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  protected function componentValidateForm(ComponentInterface $component, array $form, FormStateInterface $form_state) {
    if ($component instanceof ComponentFormInterface) {
      // @todo Remove when https://www.drupal.org/project/drupal/issues/2948549 is closed.
      $form_state->setTemporaryValue('component_form_parents', $form['component_context']['#parents']);
      $sub_form_state = SubformState::createForSubform($form['component_context'], $form, $form_state);
      $component->validateContextForm($form['component_context'], $sub_form_state);
    }
  }

  /**
   * Submits a component form.
   *
   * @param \Drupal\sfc\ComponentInterface $component
   *   A component.
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   An array of values returned by submitting the form.
   */
  protected function componentSubmitForm(ComponentInterface $component, array $form, FormStateInterface $form_state) {
    if ($component instanceof ComponentFormInterface) {
      $component_form = isset($form['component_context']) ? $form['component_context'] : NestedArray::getValue($form, $form_state->getTemporaryValue('component_form_parents'));
      $complete_form_state = $form_state instanceof SubformStateInterface ? $form_state->getCompleteFormState() : $form_state;
      $sub_form_state = SubformState::createForSubform($component_form, $form, $complete_form_state);
      $component->submitContextForm($component_form, $sub_form_state);
      return $sub_form_state->getValues();
    }
    return [];
  }

}
