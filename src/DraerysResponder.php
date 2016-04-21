<?php
/**
 * @file
 * Contains \Draerys\DraerysResponder
 */

namespace Draerys;

use Aerys\{function parseBody};
use Aerys\Request as AerysRequest;
use Aerys\Response;
use Draerys\Http\Request;

class DraerysResponder {

  /**
   * @var \Draerys\DraerysKernel
   */
  protected $kernel;

  /**
   * Constructor for the Responder.
   */
  public function __construct(DraerysKernel $kernel) {
    $this->kernel = $kernel;
  }

  /**
   * Decide whether to defer the request to another responder.
   */
  protected function deferRequest(AerysRequest $req) {
    // Do not use this responder if we're accessing a file in the default files.
    if (substr($req->getUri(), 0, 20) == '/sites/default/files') {
      return TRUE;
    }

    // Do not use this responder if we're accessing a file with an extension as
    // this probably isn't a drupal page.
    if (!in_array(pathinfo($req->getUri(), PATHINFO_EXTENSION), ['', 'php', 'inc', 'module'])) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Invoke callback.
   */
  public function __invoke(AerysRequest $req, Response $resp) {
    if ($this->deferRequest($req)) {
      return;
    }

    // Try and move out of handler.
    $this->kernel->boot();
    $buffered_content = yield $req->getBody();
    $parsed_body = yield parseBody($req);
    $post = $parsed_body->getAll();
    $request = Request::createFromAerysRequest($req, $post['fields'] ? $post['fields'] : [], $buffered_content);

    // Build the symfony response from the request.
    $result = $this->kernel->handle($request);

    // Pop the request we just added off of the request stack.
    // @todo: This is a work around. To do non-blocking Drupal properly we need
    // to find a way not to rely on a static current path service/request stack.
    $request_stack = $this->kernel->getContainer()->get('request_stack');
    $request_stack->pop();

    // Write all headers out.
    foreach ($result->headers->allPreserveCase() as $key => $value) {
      if (is_array($value)) {
        $value = reset($value);
      }
      $resp->setHeader($key, $value);
    }

    // Write out all cookies.
    foreach ($result->headers->getCookies() as $cookie) {
      $resp->setCookie($cookie->getName(), $cookie->getValue(), [
        'Expires' => $cookie->getExpiresTime(),
        'Path' => $cookie->getPath(),
        'Domain' => $cookie->getDomain(),
        'Secure' => $cookie->isSecure(),
        'HttpOnly' => $cookie->isHttpOnly(),
      ]);
    }

    // Send page content
    $resp->end($result->getContent());
    $this->kernel->terminate($request, $result);
  }
}