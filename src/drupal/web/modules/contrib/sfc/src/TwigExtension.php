<?php

namespace Drupal\sfc;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Render\RendererInterface;
use Twig\TwigFunction;
use Drupal\Component\Plugin\PluginManagerInterface;
use Twig\Extension\AbstractExtension;

/**
 * Contains custom Twig integrations for the Single File Components module.
 */
class TwigExtension extends AbstractExtension {

  /**
   * The plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $manager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * TwigExtension constructor.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $manager
   *   The plugin manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(PluginManagerInterface $manager, RendererInterface $renderer) {
    $this->manager = $manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('sfc_prepare_context', [$this, 'prepareContext'], ['needs_context' => TRUE]),
      new TwigFunction('sfc_cache', [$this, 'cache']),
    ];
  }

  /**
   * Allows component plugins to modify the template context.
   *
   * @param array &$context
   *   The context.
   * @param string $id
   *   The plugin ID.
   */
  public function prepareContext(array &$context, $id) {
    /** @var \Drupal\sfc\ComponentInterface $component */
    $component = $this->manager->createInstance($id);
    $component->prepareContext($context);
  }

  /**
   * Allows component plugins to quickly add caching to templates.
   *
   * @param mixed $arg
   *   String, Object or Array.
   * @param string $type
   *   The type of metadata for string $args. Defaults to "tags".
   *   Valid options are "contexts", "max-age", or "tags".
   *
   * @return mixed
   *   The rendered output.
   */
  public function cache($arg, $type = 'tags') {
    $build = [];
    $metadata = new CacheableMetadata();
    $scalars = [];

    if (!is_array($arg)) {
      $arg = [$arg];
    }

    foreach ($arg as $current) {
      if (is_scalar($current)) {
        $scalars[] = $current;
      }
      elseif ($current instanceof CacheableDependencyInterface) {
        $metadata = CacheableMetadata::createFromObject($current)->merge($metadata);
      }
    }

    if (!empty($scalars)) {
      switch ($type) {
        case 'contexts':
          $metadata->addCacheContexts($scalars);
          break;

        case 'max-age':
          $metadata->setCacheMaxAge($scalars[0]);
          break;

        default:
          $metadata->addCacheTags($scalars);
      }
    }

    $metadata->applyTo($build);
    return $this->renderer->render($build);
  }

}
