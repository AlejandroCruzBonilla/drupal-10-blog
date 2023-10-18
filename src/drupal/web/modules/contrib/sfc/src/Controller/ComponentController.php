<?php

namespace Drupal\sfc\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Enables projects to quickly define routes for components.
 */
class ComponentController extends ControllerBase {

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a ComponentController instance.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(RendererInterface $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
    );
  }

  /**
   * Generic controller for rendering components.
   *
   * To use this method, define a route like:
   *
   * my_route:
   *   path: /homepage
   *   defaults:
   *     _controller: \Drupal\sfc\Controller\ComponentController::build
   *     component_id: homepage
   *   requirements:
   *     _access: 'TRUE'
   *
   * Other route parameters will be passed directly into context, and the
   * current request will be available in the "request" key.
   *
   * @param string $component_id
   *   The component ID.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param bool $anon_session
   *   (optional) Force an anonymous session to be created.
   *
   * @return array|\Drupal\Core\Render\HtmlResponse
   *   The render array.
   */
  public function build($component_id, Request $request, $anon_session = FALSE) {
    if ($anon_session && $this->currentUser()->isAnonymous()) {
      $request->getSession()->set('forced', TRUE);
    }
    $context = $request->attributes->all();
    $context['request'] = $request;
    $build = [
      'component' => [
        '#type' => 'sfc',
        '#component_id' => $component_id,
        '#context' => $context,
      ],
    ];
    return $build;
  }

}
