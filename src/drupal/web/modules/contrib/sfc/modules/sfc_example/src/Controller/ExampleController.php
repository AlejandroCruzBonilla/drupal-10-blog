<?php

namespace Drupal\sfc_example\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Contains routes for examples.
 */
class ExampleController extends ControllerBase {

  /**
   * Returns a random cat breed.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The response.
   */
  public function randomCatBreed() {
    $breeds = _sfc_example_get_cat_breeds();
    return new JsonResponse([
      'breed' => $breeds[array_rand($breeds)],
    ]);
  }

}
