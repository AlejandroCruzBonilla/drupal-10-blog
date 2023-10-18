<?php

namespace Drupal\big_pipe_sessionless\StackMiddleware;

use Drupal\page_cache\StackMiddleware\PageCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Wrapper for the core PageCache middleware.
 *
 * We need control over when a response is stored in the page cache. A method
 * for this is available in the parent class but this cannot be called from
 * external code since it is protected. We are making it public.
 *
 * @see \Drupal\big_pipe_sessionless\Render\BigPipeSessionless::sendContent()
 */
class BigPipeSessionlessPageCache extends PageCache {

  /**
   * Stores a response in the page cache.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   A request object.
   * @param \Symfony\Component\HttpFoundation\Response $response
   *   A response object that should be stored in the page cache.
   *
   * @return bool
   */
  public function _storeResponse(Request $request, Response $response) {
    return $this->storeResponse($request, $response);
  }

}
