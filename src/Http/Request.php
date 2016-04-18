<?php
/**
 * @file
 * Contains \Draerys\Http\Request
 */

namespace Draerys\Http;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Aerys\Request as AerysRequest;

class Request extends SymfonyRequest {

  public static function createFromAerysRequest(AerysRequest $aerys_request) {
    // @todo: Merge getHeaders with defaults for the server.
    $request = self::create(
      $aerys_request->getUri(),
      $aerys_request->getMethod(),
      $aerys_request->getAllParams(),
      array(),
      array(),
      $aerys_request->getAllHeaders()
    );
    return $request;
  }
}