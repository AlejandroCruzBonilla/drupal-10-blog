<?php

namespace Drupal\sfc\Controller;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\sfc\ComponentActionsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller that performs actions on components.
 */
class ActionController extends ControllerBase {

  /**
   * The plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $manager;

  /**
   * ActionController constructor.
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
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.single_file_component')
    );
  }

  /**
   * Performs an action for the given component.
   *
   * @param string $component_id
   *   The ID (name) of the component.
   * @param string $action
   *   The name of the action.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   */
  public function perform($component_id, $action, Request $request) {
    /** @var \Drupal\sfc\ComponentActionsInterface $instance */
    $component = $this->manager->createInstance($component_id);
    if (!($component instanceof ComponentActionsInterface)) {
      throw new NotFoundHttpException('Component does not support actions');
    }
    return $component->performAction($action, $request);
  }

}
