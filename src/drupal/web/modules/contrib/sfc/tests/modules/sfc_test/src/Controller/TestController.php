<?php

namespace Drupal\sfc_test\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Contains test routes for the sfc_test module.
 *
 * @codeCoverageIgnore
 */
class TestController extends ControllerBase {

  /**
   * Returns a test response for the say_hello component.
   *
   * @return array
   *   A render array.
   */
  public function sayHelloDefault() {
    return [
      '#type' => 'inline_template',
      '#template' => '{% include "sfc--say-hello.html.twig" %}',
    ];
  }

  /**
   * Returns a test response for the say_hello component with context.
   *
   * @return array
   *   A render array.
   */
  public function sayHelloName() {
    return [
      '#type' => 'inline_template',
      '#template' => '{% include "sfc--say-hello.html.twig" with {"name": "Sam"} %}',
    ];
  }

  /**
   * Renders an arbitrary component using context stored in key value store.
   *
   * @param string $plugin_id
   *   The plugin ID.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return array
   *   A render array.
   */
  public function renderComponent($plugin_id, Request $request) {
    if ($request->query->has('sfc_test_context')) {
      $context = json_decode($request->query->get('sfc_test_context'));
    }
    else {
      $context = unserialize(\Drupal::keyValue('sfc_test')->get('sfc_test_context'));
    }
    if (empty($context)) {
      $context = [];
    }
    return [
      '#type' => 'sfc',
      '#component_id' => $plugin_id,
      '#context' => $context,
    ];
  }

}
