<?php

namespace Drupal\sfc\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\sfc\ComponentConsumerTrait;
use Drupal\sfc\ComponentInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a field formatter for single file components.
 *
 * @FieldFormatter(
 *   id = "single_file_component_field_formatter",
 *   deriver = "\Drupal\sfc\Plugin\Derivative\ComponentDeriver",
 *   sfc_key = "field_formatter",
 * )
 */
class ComponentFieldFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  use ComponentConsumerTrait;

  /**
   * A single file component instance.
   *
   * @var \Drupal\sfc\ComponentInterface
   */
  protected $component;

  /**
   * Constructs a ComponentFieldFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\sfc\ComponentInterface $component
   *   A single file component instance.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, ComponentInterface $component) {
    $this->component = $component;
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    list (, $component_plugin_id) = explode(static::DERIVATIVE_SEPARATOR, $plugin_id);
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('plugin.manager.single_file_component')->createInstance($component_plugin_id)
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'component_context' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $settings = $this->getSettings();
    $base_component_context = isset($settings['component_context']) ? $settings['component_context'] : [];
    $build = [];
    // Components can support single field items and use the default wrappers,
    // or support multiple field items.
    if (!empty($this->pluginDefinition['sfc_multiple'])) {
      $component_context = $base_component_context;
      $component_context['items'] = $items;
      $component_context['langcode'] = $langcode;
      $build[] = $this->componentBuild($this->component, $component_context);
    }
    else {
      foreach ($items as $item) {
        $component_context = $base_component_context;
        $component_context['item'] = $item;
        $component_context['langcode'] = $langcode;
        $build[] = $this->componentBuild($this->component, $component_context);
      }
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = [];
    $element['header']['#type'] = 'container';
    $settings = $this->getSettings();
    $component_context = isset($settings['component_context']) ? $settings['component_context'] : [];
    $element = $this->componentBuildForm($this->component, $component_context, $element);
    return $element;
  }

}
