<?php

namespace Drupal\sfc;

use Symfony\Component\HttpFoundation\Request;

/**
 * An interface for single file components with actions.
 */
interface ComponentActionsInterface {

  /**
   * Performs an action.
   *
   * Actions are basically HTTP requests without needing your own route or
   * controller. As a result, the implementer should perform access checks in
   * this method and also validate input coming from the $request.
   *
   * @param string $name
   *   The action name.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return mixed
   *   The action response - should resemble the return from a controller.
   */
  public function performAction($name, Request $request);

}
