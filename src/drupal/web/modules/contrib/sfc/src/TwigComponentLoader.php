<?php

namespace Drupal\sfc;

require_once __DIR__ . '/LoaderInterfaceShim.php';

use Twig\Source;
use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;
use Twig\Loader\ExistsLoaderInterface;
use Twig\Loader\SourceContextLoaderInterface;
use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Loads templates from component instances.
 */
class TwigComponentLoader implements LoaderInterface, ExistsLoaderInterface, SourceContextLoaderInterface {

  /**
   * The plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $manager;

  /**
   * TwigComponentLoader constructor.
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
  public function getSource($name) {
    return $this->getTemplate($name);
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceContext($name): Source {
    $source = $this->getTemplate($name);
    $source = $source ? $source : '';
    return new Source($source, $name);
  }

  /**
   * {@inheritdoc}
   */
  public function exists($name) {
    return (bool) $this->getTemplate($name);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheKey($name): string {
    if (!($template = $this->getTemplate($name))) {
      throw new LoaderError(sprintf('Template "%s" is not defined.', $name));
    }

    return $name . ':' . $template;
  }

  /**
   * {@inheritdoc}
   */
  public function isFresh($name, $time): bool {
    if (!$this->getTemplate($name)) {
      throw new LoaderError(sprintf('Template "%s" is not defined.', $name));
    }

    return TRUE;
  }

  /**
   * Gets the template string from the single file component class.
   *
   * @param string $name
   *   The template name.
   *
   * @return bool|string
   *   The template, or FALSE if this is not a single file component.
   */
  protected function getTemplate($name) {
    $name = preg_replace('/^sfc\//', '', $name);
    if (ComponentNameHelper::isComponentTemplate($name)) {
      $match = ComponentNameHelper::getIdFromTemplateName($name);
      return $this->manager->createInstance($match)->getTemplate();
    }
    foreach ($this->manager->getDefinitions() as $id => $definition) {
      if (isset($definition['aliases']) && in_array($name, $definition['aliases'], TRUE)) {
        return $this->manager->createInstance($id)->getTemplate();
      }
    }
    return FALSE;
  }

}
