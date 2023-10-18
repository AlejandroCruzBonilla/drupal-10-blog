<?php

namespace Drupal\sfc;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages single file component provider plugins.
 */
class ComponentPluginManager extends DefaultPluginManager {

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Constructs a new ComponentPluginManager.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler) {
    parent::__construct('Plugin/SingleFileComponent', $namespaces, $module_handler, 'Drupal\sfc\ComponentInterface', 'Drupal\sfc\Annotation\SingleFileComponent');
    $this->alterInfo('single_file_component');
    $this->setCacheBackend($cache_backend, 'single_file_component_provider_plugins');
    $this->themeHandler = $theme_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = []) {
    // The alternate ID can be used to alias a component plugin that may have a
    // long plugin ID, like a derivative.
    if (!$this->hasDefinition($plugin_id)) {
      foreach ($this->getDefinitions() as $id => $definition) {
        if (isset($definition['alt_id']) && $definition['alt_id'] === $plugin_id) {
          $plugin_id = $id;
          break;
        }
      }
    }
    return parent::createInstance($plugin_id, $configuration);
  }

  /**
   * {@inheritdoc}
   */
  protected function providerExists($provider) {
    return $this->moduleHandler->moduleExists($provider) || $this->themeHandler->themeExists($provider);
  }

}
