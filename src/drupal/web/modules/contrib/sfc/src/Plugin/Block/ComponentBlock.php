<?php

namespace Drupal\sfc\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\sfc\ComponentConsumerTrait;
use Drupal\sfc\ComponentInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block for single file components.
 *
 * @Block(
 *   id = "single_file_component_block",
 *   deriver = "\Drupal\sfc\Plugin\Derivative\ComponentDeriver",
 *   sfc_key = "block",
 *   category = "Components"
 * )
 */
class ComponentBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use ComponentConsumerTrait;

  /**
   * A single file component instance.
   *
   * @var \Drupal\sfc\ComponentInterface
   */
  protected $component;

  /**
   * Constructs a new ComponentBlock.
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
  public function build() {
    $configuration = $this->getConfiguration();
    $component_context = isset($configuration['component_context']) ? $configuration['component_context'] : [];
    return $this->componentBuild($this->component, $component_context);
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
  public function blockForm($form, FormStateInterface $form_state) {
    $configuration = $this->getConfiguration();
    $component_context = isset($configuration['component_context']) ? $configuration['component_context'] : [];
    return $this->componentBuildForm($this->component, $component_context, $form);
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $this->componentValidateForm($this->component, $form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['component_context'] = $this->componentSubmitForm($this->component, $form, $form_state);
  }

}
