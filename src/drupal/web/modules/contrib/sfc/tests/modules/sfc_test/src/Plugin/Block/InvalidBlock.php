<?php

namespace Drupal\sfc_test\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\sfc\ComponentConsumerTrait;
use Drupal\sfc\ComponentInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a plugin base that does not contain the sfc_dev key.
 *
 * @Block(
 *   id = "single_file_component_invalid_block",
 *   deriver = "\Drupal\sfc\Plugin\Derivative\ComponentDeriver",
 *   category = "Components"
 * )
 *
 * @codeCoverageIgnore
 */
class InvalidBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use ComponentConsumerTrait;

  /**
   * A single file component instance.
   *
   * @var \Drupal\sfc\ComponentInterface
   */
  protected $component;

  /**
   * Constructs a new InvalidBlock.
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
    return [];
  }

}
