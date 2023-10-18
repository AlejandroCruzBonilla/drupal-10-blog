<?php

namespace Drupal\sfc\Commands;

use Consolidation\SiteAlias\SiteAliasManagerAwareTrait;
use Drupal\Component\Plugin\Discovery\CachedDiscoveryInterface;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Asset\LibraryDiscoveryInterface;
use Drupal\sfc\ComponentFilenameInterface;
use Drush\Commands\DrushCommands;
use Drush\SiteAlias\SiteAliasManagerAwareInterface;

/**
 * Drush command file for SFC commands.
 */
class ComponentCommands extends DrushCommands implements SiteAliasManagerAwareInterface {

  use SiteAliasManagerAwareTrait;

  /**
   * The plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $manager;

  /**
   * The watch file path.
   *
   * @var string
   */
  protected $watchFilePath;

  /**
   * The library discovery service.
   *
   * @var \Drupal\Core\Asset\LibraryDiscoveryInterface
   */
  protected $libraryDiscovery;

  /**
   * ComponentCommands constructor.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $manager
   *   The plugin manager.
   * @param string $watch_file_path
   *   The watch file path.
   * @param \Drupal\Core\Asset\LibraryDiscoveryInterface $library_discovery
   *   The simple component deriver.
   */
  public function __construct(PluginManagerInterface $manager, $watch_file_path = 'public://sfc_watch_file.txt', LibraryDiscoveryInterface $library_discovery = NULL) {
    $this->manager = $manager;
    $this->watchFilePath = $watch_file_path;
    if (!$library_discovery) {
      $library_discovery = \Drupal::service('library.discovery');
    }
    $this->libraryDiscovery = $library_discovery;
  }

  /**
   * Writes the assets and/or source for a given component.
   *
   * @param string $id
   *   The plugin ID.
   *
   * @command sfc:write
   */
  public function write($id) {
    /** @var \Drupal\sfc\ComponentInterface $component */
    $component = $this->manager->createInstance($id);
    $component->writeAssets();
  }

  /**
   * Watches for changes in all components.
   *
   * This is a good alternative to disabling the "data" cache bin for normal
   * components.
   *
   * @param array $options
   *   Options for this command.
   *
   * @command sfc:watch
   * @option run-once If the command should only be run once.
   */
  public function watch(array $options = ['run-once' => FALSE]) {
    $this->io()->writeln('Watching for changes...');
    $last_error = '';
    while (TRUE) {
      /** @var \Consolidation\SiteProcess\ProcessBase $process */
      $process = $this->processManager()->drush($this->siteAliasManager()->getSelf(), 'sfc:do-watch');
      $process->run();

      // Do not repeat errors in the loop.
      $error = $process->getErrorOutput();
      if ($last_error && !$error) {
        $this->io()->success('Errors resolved! Still watching...');
      }
      elseif ($last_error !== $error) {
        $this->io()->error($error);
        $process->clearErrorOutput();
        // Clearing cached definitions handles deleted components.
        if ($this->manager instanceof CachedDiscoveryInterface) {
          $this->manager->clearCachedDefinitions();
        }
      }
      $last_error = $error;

      $output = $process->getOutput();
      if (!$error && $output) {
        echo $output;
      }

      usleep(250000);
      if ($options['run-once']) {
        break;
      }
    }
    return 0;
  }

  /**
   * Writes components in another command to avoid killing "sfc:watch".
   *
   * @command sfc:do-watch
   */
  public function doWatch() {
    $last_cache_clear = @file_get_contents($this->watchFilePath) ?: time();

    // See if definitions changed, in which case all cache should be cleared.
    if ($this->manager instanceof CachedDiscoveryInterface) {
      $hash = md5(serialize($this->manager->getDefinitions()) . serialize($this->libraryDiscovery->getLibrariesByExtension('sfc')));
      $this->manager->clearCachedDefinitions();
      $this->libraryDiscovery->clearCachedDefinitions();
      if ($hash !== md5(serialize($this->manager->getDefinitions()) . serialize($this->libraryDiscovery->getLibrariesByExtension('sfc')))) {
        $this->io()->writeln('Definitions or libraries changed, clearing all cache');
        $this->processManager()->drush($this->siteAliasManager()->getSelf(), 'cr', [])->mustRun();
        file_put_contents($this->watchFilePath, time());
      }
    }

    $definitions = $this->manager->getDefinitions();
    if (empty($definitions)) {
      return 0;
    }

    clearstatcache();
    $clear_cache = FALSE;
    foreach (array_keys($definitions) as $id) {
      /** @var \Drupal\sfc\ComponentInterface $component */
      $component = $this->manager->createInstance($id);
      if ($component->shouldWriteAssets()) {
        $this->io()->writeln("Writing assets for $id");
        $this->processManager()->drush($this->siteAliasManager()->getSelf(), 'sfc:write', [$id])->mustRun();
        $clear_cache = TRUE;
      }
      if ($component instanceof ComponentFilenameInterface && filemtime($component->getComponentFileName()) > $last_cache_clear) {
        $clear_cache = TRUE;
      }
    }
    if ($clear_cache) {
      $this->io()->writeln("Clearing render cache");
      $this->processManager()->drush($this->siteAliasManager()->getSelf(), 'cc', ['render'])->mustRun();
      file_put_contents($this->watchFilePath, time());
    }

    return 0;
  }

}
