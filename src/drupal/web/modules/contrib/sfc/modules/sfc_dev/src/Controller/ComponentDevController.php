<?php

namespace Drupal\sfc_dev\Controller;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Asset\AssetResolverInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\sfc_dev\Ajax\RefreshComponentAssetsCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Render\BareHtmlPageRendererInterface;

/**
 * Contains routes for the sfc_dev module.
 */
class ComponentDevController extends ControllerBase {

  /**
   * The component plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $manager;

  /**
   * The asset resolver.
   *
   * @var \Drupal\Core\Asset\AssetResolverInterface
   */
  protected $assetResolver;

  /**
   * The bare HTML page renderer.
   *
   * @var \Drupal\Core\Render\BareHtmlPageRendererInterface
   */
  protected $bareHtmlPageRenderer;

  /**
   * ComponentDevController constructor.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $manager
   *   The component plugin manager.
   * @param \Drupal\Core\Asset\AssetResolverInterface $asset_resolver
   *   The asset resolver.
   * @param \Drupal\Core\Render\BareHtmlPageRendererInterface|null $bare_html_page_renderer
   *   The bare HTML page renderer.
   */
  public function __construct(PluginManagerInterface $manager, AssetResolverInterface $asset_resolver, BareHtmlPageRendererInterface $bare_html_page_renderer = NULL) {
    $this->manager = $manager;
    $this->assetResolver = $asset_resolver;
    if (!$bare_html_page_renderer) {
      $bare_html_page_renderer = \Drupal::service('bare_html_page_renderer');
    }
    $this->bareHtmlPageRenderer = $bare_html_page_renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.single_file_component'),
      $container->get('asset.resolver'),
      $container->get('bare_html_page_renderer')
    );
  }

  /**
   * Displays the component library in a normal page.
   *
   * @param string $group
   *   A group to filter by, if provided.
   *
   * @return array
   *   A render array.
   */
  public function libraryBase($group = NULL) {
    $grouped_definitions = [];
    foreach ($this->manager->getDefinitions() as $plugin_id => $definition) {
      $id = isset($definition['alt_id']) ? $definition['alt_id'] : $plugin_id;
      if (isset($definition['group']) && (!$group || strpos($definition['group'], $group) !== FALSE)) {
        $grouped_definitions[$definition['group']][$id] = $definition;
      }
      elseif (!$group) {
        $grouped_definitions['Other'][$id] = $definition;
      }
    }
    foreach ($grouped_definitions as &$definitions) {
      ksort($definitions, SORT_STRING | SORT_FLAG_CASE);
    }
    ksort($grouped_definitions, SORT_STRING | SORT_FLAG_CASE);
    return [
      '#type' => 'inline_template',
      '#template' => '{% include "sfc--sfc-dev-component-library.html.twig" %}',
      '#context' => [
        'grouped_definitions' => $grouped_definitions,
      ],
    ];
  }

  /**
   * Displays the component library in full screen.
   *
   * @param string $group
   *   A group to filter by, if provided.
   *
   * @return \Drupal\Core\Render\HtmlResponse
   *   A rendered HTML response.
   */
  public function library($group = NULL) {
    return $this->bareHtmlPageRenderer->renderBarePage($this->libraryBase($group), 'Single File Component Library', 'sfc_dev_library', []);
  }

  /**
   * AJAX callback for refreshing the library preview.
   *
   * @param string $plugin_id
   *   The plugin ID.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The response.
   */
  public function libraryPreview($plugin_id) {
    $component = $this->manager->createInstance($plugin_id);
    $context = [
      'component' => $component,
    ];
    $response = new AjaxResponse();
    $content = [
      '#type' => 'inline_template',
      '#template' => '{% include "sfc--sfc-dev-component-preview.html.twig" %}',
      '#context' => $context,
      '#attached' => [
        'library' => [
          'sfc_dev/main',
        ],
      ],
    ];
    $command = new ReplaceCommand('.js-component-preview', $content);
    $response->addCommand($command);
    $command = new InvokeCommand('.js-component-preview :tabbable:first', 'focus');
    $response->addCommand($command);
    $command = new InvokeCommand('[data-component-picker-id]', 'removeClass', ['active']);
    $response->addCommand($command);
    $command = new InvokeCommand('[data-component-picker-id="' . $plugin_id . '"]', 'addClass', ['active']);
    $response->addCommand($command);
    $command = new RefreshComponentAssetsCommand($component, $this->assetResolver);
    $response->addCommand($command);
    return $response;
  }

  /**
   * AJAX callback for checking if a component is outdated.
   *
   * @param string $plugin_id
   *   The plugin ID.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The response.
   */
  public function shouldWriteAssets($plugin_id) {
    $response = new JsonResponse();
    $response->setContent(Json::encode($this->manager->createInstance($plugin_id)->shouldWriteAssets()));
    return $response;
  }

  /**
   * AJAX callback for viewing a component template.
   *
   * @param string $plugin_id
   *   The plugin ID.
   *
   * @return array
   *   The render array.
   */
  public function viewTemplate($plugin_id) {
    $build = [
      '#type' => 'inline_template',
      '#template' => '{% include "sfc--sfc-dev-component-template.html.twig" %}',
      '#context' => [
        'component' => $this->manager->createInstance($plugin_id),
      ],
    ];
    return $build;
  }

  /**
   * Title callback for the ::viewTemplate route.
   *
   * @param string $plugin_id
   *   The plugin ID.
   *
   * @return string
   *   The title.
   */
  public function viewTemplateTitle($plugin_id) {
    return $this->t('Template for @plugin_id', [
      '@plugin_id' => $plugin_id,
    ]);
  }

}
