<?php
/**
 * @file
 * Contains \Draerys\Http\Request
 */

namespace Draerys\Http;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Aerys\Request as AerysRequest;

class Request extends SymfonyRequest {

  public static function createFromAerysRequest(AerysRequest $aerys_request, array $post_params = [], string $buffered_content = '') {
    // Convert post params into the correct format.
    foreach ($post_params as $key => $value) {
      if (is_array($value) && count($value) == 1) {
        $post_params[$key] = reset($value);
      }
    }

    // @todo: Merge getHeaders with defaults for the server.
    $request = self::create(
      $aerys_request->getUri(),
      $aerys_request->getMethod(),
      $aerys_request->getMethod() == 'GET' ? $aerys_request->getAllParams() : $post_params,
      $aerys_request->getAllCookies(),
      array(),
      $aerys_request->getAllHeaders(),
      $buffered_content
    );
    print "Content: ".$buffered_content."\n";
    print "POST PARAMS: "; print_r($post_params); print "\n";
    print "Build ID: ".$request->request->get('form_build_id')."\n";
    return $request;
  }
}