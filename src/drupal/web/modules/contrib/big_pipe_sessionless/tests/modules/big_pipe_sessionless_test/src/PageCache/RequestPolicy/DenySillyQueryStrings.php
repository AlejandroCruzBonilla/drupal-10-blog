<?php

namespace Drupal\big_pipe_sessionless_test\PageCache\RequestPolicy;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * A policy denying caching of pages when there is a silly query string.
 */
class DenySillyQueryStrings implements RequestPolicyInterface {

  /**
   * {@inheritdoc}
   */
  public function check(Request $request) {
    if ($request->query->has('silly')) {
      return static::DENY;
    }

    return NULL;
  }

}
