<?php

namespace Drupal\sfc_dev\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Asset\AssetResolverInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\sfc\ComponentFormInterface;
use Drupal\sfc\ComponentInterface;
use Drupal\sfc\ComponentNameHelper;
use Drupal\sfc\ComponentConsumerTrait;
use Drupal\sfc_dev\Ajax\RefreshComponentAssetsCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Contains a form for refreshing component previews using Twig or their form.
 */
class ComponentPreviewForm extends FormBase {

  use ComponentConsumerTrait;

  /**
   * The asset resolver.
   *
   * @var \Drupal\Core\Asset\AssetResolverInterface
   */
  protected $assetResolver;

  /**
   * ComponentPreviewForm constructor.
   *
   * @param \Drupal\Core\Asset\AssetResolverInterface $asset_resolver
   *   The asset resolver.
   */
  public function __construct(AssetResolverInterface $asset_resolver) {
    $this->assetResolver = $asset_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('asset.resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sfc_dev_component_preview_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ComponentInterface $component = NULL) {
    $form['#attached']['library'][] = 'sfc_dev/main';

    $form['#component'] = $component;

    $form['#prefix'] = '<div class="js-component-preview-form">';
    $form['#suffix'] = '</div>';

    $form['messages'] = [
      '#type' => 'status_messages',
    ];

    if ($component instanceof ComponentFormInterface) {
      $form['mode'] = [
        '#type' => 'select',
        '#title' => $this->t('Testing mode'),
        '#options' => [
          'twig' => $this->t('Twig'),
          'form' => $this->t('Form'),
        ],
      ];
    }

    $form['twig'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Twig sandbox'),
      '#default_value' => '{% include "' . addcslashes(ComponentNameHelper::getTemplateName($component), "'") . '.html.twig" with {} %}',
      '#states' => [
        'visible' => [
          'select[name="mode"]' => ['value' => 'twig'],
        ],
      ],
    ];

    $form = $this->componentBuildForm($component, [], $form);
    $form['component_context']['#states'] = [
      'visible' => [
        'select[name="mode"]' => ['value' => 'form'],
      ],
    ];

    $form['auto_preview'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Auto-reload when component changes'),
      '#attributes' => [
        'class' => [
          'component-preview__auto-preview',
          'js-component-auto-preview',
        ],
        'data-component-plugin-id' => $component->getPluginId(),
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Preview'),
      '#ajax' => [
        'callback' => '::ajaxReload',
        'event' => 'click auto_preview',
      ],
      '#attributes' => [
        'class' => [
          'component-preview__button',
          'js-component-preview-button',
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $mode = $form_state->getValue('mode', 'twig');
    if ($mode === 'twig') {
      $form_state->clearErrors();
      if (preg_match('/(#|&num;|%23|convert_encoding)/', $form_state->getValue('twig'))) {
        $form_state->setError($form['twig'], $this->t('For security reasons, the "#" character and "convert_encoding" are not allowed.'));
      }
    }
    elseif ($mode === 'form') {
      $this->componentValidateForm($form['#component'], $form, $form_state);
    }
  }

  /**
   * Form AJAX callback for refreshing the form.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   A response.
   */
  public function ajaxReload(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $command = new ReplaceCommand('.js-component-preview-form', $form);
    $response->addCommand($command);
    // "component_build" is set in ::submitForm, which guarantees that the form
    // has been validated before the component is re-rendered.
    $command = new HtmlCommand('.js-component-preview-render-html', $form_state->get('component_build'));
    $response->addCommand($command);
    $command = new InvokeCommand('[data-component-picker-id]', 'removeClass', ['active']);
    $response->addCommand($command);
    $command = new InvokeCommand('[data-component-picker-id="' . $form['#component']->getPluginId() . '"]', 'addClass', ['active']);
    $response->addCommand($command);
    $command = new RefreshComponentAssetsCommand($form['#component'], $this->assetResolver);
    $response->addCommand($command);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $mode = $form_state->getValue('mode', 'twig');
    if ($mode === 'twig') {
      $form_state->set('component_build', [
        '#type' => 'inline_template',
        '#template' => $form_state->getValue('twig'),
      ]);
    }
    elseif ($mode === 'form') {
      $form_state->set('component_build', $this->componentBuild($form['#component'], $form_state->getValue('component_context', [])));
    }
  }

}
