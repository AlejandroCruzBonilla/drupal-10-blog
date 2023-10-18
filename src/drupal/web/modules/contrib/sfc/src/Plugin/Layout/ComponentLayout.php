<?php

namespace Drupal\sfc\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\Template\Attribute;
use Drupal\sfc\ComponentInterface;
use Drupal\sfc\ComponentConsumerTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a layout for single file components.
 *
 * @Layout(
 *   id = "single_file_component_layout",
 *   deriver = "\Drupal\sfc\Plugin\Derivative\ComponentDeriver",
 *   sfc_key = "layout",
 *   category = "Components"
 * )
 */
class ComponentLayout extends LayoutDefault implements ContainerFactoryPluginInterface, PluginFormInterface, TrustedCallbackInterface {

  use ComponentConsumerTrait;

  /**
   * A single file component instance.
   *
   * @var \Drupal\sfc\ComponentInterface
   */
  protected $component;

  /**
   * Constructs a new ComponentLayout.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\sfc\ComponentInterface $component
   *   A single file component instance.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ComponentInterface $component) {
    $this->component = $component;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    list (, $component_plugin_id) = explode(static::DERIVATIVE_SEPARATOR, $plugin_id);
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.single_file_component')->createInstance($component_plugin_id)
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return [
      'preRenderComponent',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'component_context' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $component_context = isset($this->configuration['component_context']) ? $this->configuration['component_context'] : [];
    $build = $this->componentBuild($this->component, $component_context);
    foreach ($this->getPluginDefinition()->getRegionNames() as $region_name) {
      if (array_key_exists($region_name, $regions)) {
        $build[$region_name] = $regions[$region_name];
      }
    }
    $build['#settings'] = $this->getConfiguration();
    $build['#layout'] = $this->pluginDefinition;
    $build['#region_names'] = $this->getPluginDefinition()->getRegionNames();
    $build['#pre_render'] = [
      [static::class, 'preRenderComponent'],
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public static function preRenderComponent(array $element) {
    // Provide the variables that would normally exist in layout templates.
    foreach ($element['#region_names'] as $region_name) {
      if (array_key_exists($region_name, $element)) {
        $element['#context']['content'][$region_name] = $element[$region_name];
        if (!isset($element['#context']['content'][$region_name]['#attributes'])) {
          $element['#context']['content'][$region_name]['#attributes'] = [];
        }
        $element['#context']['region_attributes'][$region_name] = new Attribute($element['#context']['content'][$region_name]['#attributes']);
        unset($element[$region_name]);
      }
    }
    return ComponentConsumerTrait::preRenderComponent($element);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $component_context = isset($this->configuration['component_context']) ? $this->configuration['component_context'] : [];
    return $this->componentBuildForm($this->component, $component_context, $form);
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->componentValidateForm($this->component, $form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['component_context'] = $this->componentSubmitForm($this->component, $form, $form_state);
  }

}
