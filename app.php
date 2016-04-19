<?php
/**
 * @file
 * Drearys application file.
 */

use Aerys\{function root};
use Aerys\Host;
use Aerys\Request as AerysRequest;
use Aerys\Response;
use Draerys\DraerysKernel;
use Draerys\Http\Request;

$autoloader = require 'vendor/autoload.php';

$kernel = new DraerysKernel('prod', $autoloader);
$kernel->setSitePath('sites/default');

$host = new Host();
$host->use(function(AerysRequest $req, Response $resp) use (&$kernel) {
  if (substr($req->getUri(), 0, 20) == '/sites/default/files') {
    return;
  }

  if (!in_array(pathinfo($req->getUri(), PATHINFO_EXTENSION), ['', 'php', 'inc', 'module'])) {
    return;
  }

  $buffered_content = yield $req->getBody();
  $parsed_body = yield Aerys\parseBody($req);
  $post = $parsed_body->getAll();
  $request = Request::createFromAerysRequest($req, $post['fields'] ? $post['fields'] : [], $buffered_content);
  $result = $kernel->handle($request);

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
});
$host->use(root(__DIR__));
