<?php

namespace Drupal\Tests\sfc\Kernel;

use Drupal\Core\Render\RenderContext;
use Drupal\sfc\ComponentInterface;

/**
 * Contains useful methods for testing single file components.
 */
trait ComponentTestTrait {

  /**
   * Renders a single file component by ID.
   *
   * This is useful for integration testing since it renders your component as
   * it would be rendered (included) in a theme template.
   *
   * @param string $plugin_id
   *   The component plugin ID.
   * @param array $context
   *   The template context.
   *
   * @return string
   *   The rendered HTML.
   */
  protected function renderComponent($plugin_id, array $context) {
    /** @var \Drupal\Core\Template\TwigEnvironment $environment */
    $environment = \Drupal::service('twig');
    $cache = $environment->getCache();
    $environment->setCache(FALSE);

    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = \Drupal::service('renderer');
    $element = [
      '#type' => 'inline_template',
      '#template' => '{% include "sfc--' . str_replace('_', '-', $plugin_id) . '.html.twig" %}',
      '#context' => $context,
    ];
    $return = $renderer->renderPlain($element);
    $environment->setCache($cache);
    return (string) $return;
  }

  /**
   * Renders a single file component object.
   *
   * This is useful for mocking since it calls methods on the object directly
   * but that could also mean that the HTML isn't exactly the same as it would
   * be when normally included.
   *
   * @param \Drupal\sfc\ComponentInterface $component
   *   The component.
   * @param array $context
   *   The template context.
   *
   * @return string
   *   The rendered HTML.
   */
  protected function renderComponentObject(ComponentInterface $component, array $context) {
    /** @var \Drupal\Core\Template\TwigEnvironment $environment */
    $environment = \Drupal::service('twig');
    $cache = $environment->getCache();
    $environment->setCache(FALSE);

    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = \Drupal::service('renderer');
    $component->prepareContext($context);
    $render_context = new RenderContext();
    $return = $renderer->executeInRenderContext($render_context, function () use ($environment, $component, $context) {
      return $environment->renderInline($component->getTemplate(), $context);
    });
    $environment->setCache($cache);
    return (string) $return;
  }

}
