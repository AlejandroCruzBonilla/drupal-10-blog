<?php

namespace Drupal\sfc\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Derives SFC plugin definitions from .sfc files.
 */
class SimpleComponentDeriver extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  public const AUTOWIRE_CURRENT_REQUEST = '_current_request';

  public const AUTOWIRE_IGNORE = '_ignore';

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The container.
   *
   * @var \Drupal\Component\DependencyInjection\Container
   */
  protected $container;

  /**
   * A cache mapping argument types (classes/interfaces) to service IDs.
   *
   * @var array
   */
  protected $autoWireCache = [];

  /**
   * SimpleComponentDeriver constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container.
   */
  public function __construct(ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler, FileSystemInterface $file_system, ContainerInterface $container = NULL) {
    $this->moduleHandler = $module_handler;
    $this->themeHandler = $theme_handler;
    $this->fileSystem = $file_system;
    $this->container = $container ? $container : \Drupal::getContainer();
    $this->autoWireCache[Request::class] = self::AUTOWIRE_CURRENT_REQUEST;
    $this->autoWireCache[FormStateInterface::class] = self::AUTOWIRE_IGNORE;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('module_handler'),
      $container->get('theme_handler'),
      $container->get('file_system'),
      $container
    );
  }

  /**
   * Gets arguments that can be used to autowire a callback.
   *
   * @param callable $callback
   *   The callback.
   *
   * @return array
   *   An array mapping param indexes to service IDs.
   */
  public function getAutowireArgs(callable $callback) {
    $args = [];
    $reflection = new \ReflectionFunction($callback);
    foreach ($reflection->getParameters() as $i => $param) {
      $type_obj = $param->getType();
      if (!$type_obj || ($type_obj instanceof \ReflectionNamedType && $type_obj->isBuiltin())) {
        $args[$i] = self::AUTOWIRE_IGNORE;
        continue;
      }
      $type = $type_obj->getName();
      if (isset($this->autoWireCache[$type])) {
        $args[$i] = $this->autoWireCache[$type];
        continue;
      }
      $match = FALSE;
      $should_cache = TRUE;
      foreach ($this->container->getServiceIds() as $service_id) {
        try {
          $service = $this->container->get($service_id);
        } catch (\Exception $e) {
          continue;
        }
        if ($service === $type) {
          $match = $service_id;
          break;
        }
        elseif ($service instanceof $type) {
          $match = $service_id;
          if ($param->getName() === str_replace('.', '_', $service_id)) {
            $should_cache = FALSE;
            break;
          }
        }
      }
      if (!$match) {
        throw new \Exception("Cannot autowire parameter $i ($type)");
      }
      if ($should_cache) {
        $autoWireCache[$type] = $match;
      }
      $args[$i] = $match;
    }
    return $args;
  }

  /**
   * Builds autowire information for a component's callbacks.
   *
   * @param string $id
   *   The component derivative ID.
   */
  public function autoWire($id) {
    $contents = sfc_require($this->derivatives[$id]['simple_file']);
    if (isset($contents['actions'])) {
      foreach ($contents['actions'] as $action => $callback) {
        $this->derivatives[$id]['action_autowire'][$action] = $this->getAutowireArgs($callback);
      }
    }
    $callbacks = [
      'prepareContext',
      'buildContextForm',
      'validateContextForm',
      'submitContextForm',
    ];
    foreach ($callbacks as $callback) {
      if (isset($contents[$callback]) && is_callable($contents[$callback])) {
        $this->derivatives[$id]['callback_autowire'][$callback] = $this->getAutowireArgs($contents[$callback]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $directories = array_merge($this->moduleHandler->getModuleDirectories(), $this->themeHandler->getThemeDirectories());
    // Ensure the default theme is loaded last.
    $default_theme = $this->themeHandler->getDefault();
    if (isset($directories[$default_theme])) {
      $path = $directories[$default_theme];
      unset($directories[$default_theme]);
      $directories[$default_theme] = $path;
    }
    foreach ($directories as $project => $directory) {
      if (!is_dir($directory . '/components/')) {
        continue;
      }
      foreach ($this->fileSystem->scanDirectory($directory . '/components/', '/^.*\.sfc/') as $file) {
        $id = str_replace('.sfc', '', $file->filename);
        $this->derivatives[$id] = $base_plugin_definition;
        $this->derivatives[$id]['simple_file'] = $file->uri;
        $this->derivatives[$id]['alt_id'] = $id;
        $this->derivatives[$id]['provider'] = $project;
        $this->derivatives[$id]['action_autowire'] = [];
        $this->autoWire($id);
        $this->derivatives[$id] = array_merge($this->derivatives[$id], $this->getDefinitionAdditions($file->uri));
      }
    }
    return $this->derivatives;
  }

  /**
   * Gets plugin definition additions defined by a simple component file.
   *
   * This is in a separate function to avoid variables in the .sfc file
   * polluting the ::getDerivativeDefinitions scope.
   *
   * @param string $filename
   *   The simple component filename.
   *
   * @return array
   *   An array representing the plugin definition additions.
   */
  protected function getDefinitionAdditions($filename) {
    $contents = sfc_require($filename);
    return isset($contents['definition']) ? $contents['definition'] : [];
  }

}
