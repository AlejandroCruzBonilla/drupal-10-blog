<?php

namespace Drupal\sfc\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Layout\LayoutDefinition;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Derives plugin definitions abstractly based on annotation values.
 *
 * To use this deriver, include a "sfc_key" key in your base class'
 * annotation that indicates what annotation key components should use to
 * place definition information.
 */
class ComponentDeriver extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $manager;

  /**
   * ComponentDeriver constructor.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $manager
   *   The plugin manager.
   */
  public function __construct(PluginManagerInterface $manager) {
    $this->manager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('plugin.manager.single_file_component')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    if ($base_plugin_definition instanceof LayoutDefinition) {
      $sfc_key = $base_plugin_definition->get('sfc_key');
    }
    elseif (is_array($base_plugin_definition) && isset($base_plugin_definition['sfc_key'])) {
      $sfc_key = $base_plugin_definition['sfc_key'];
    }
    else {
      return $this->derivatives;
    }
    // Generically allow components to derive plugins from their definition.
    foreach ($this->manager->getDefinitions() as $plugin_id => $definition) {
      // Prefer the alternative ID if available.
      if (isset($definition['alt_id'])) {
        $plugin_id = $definition['alt_id'];
      }
      if (!empty($definition[$sfc_key])) {
        if ($base_plugin_definition instanceof LayoutDefinition) {
          $derivative = clone $base_plugin_definition;
          foreach ($definition[$sfc_key] as $key => $value) {
            $derivative->set($key, $value);
          }
          $derivative->set('provider', $definition['provider']);
          $this->derivatives[$plugin_id] = $derivative;
        }
        elseif (is_array($definition)) {
          $derivative = $definition[$sfc_key] + $base_plugin_definition;
          $derivative['provider'] = $definition['provider'];
          $this->derivatives[$plugin_id] = $derivative;
        }
      }
    }
    return $this->derivatives;
  }

}
