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
use Drupal\Core\Database\Database;

$autoloader = require 'vendor/autoload.php';

$site_path = 'sites/default';

$kernel = new DraerysKernel('prod', $autoloader);
$kernel->bootEnvironment();
$kernel->setSitePath($site_path);

$host = new Host();
$host->use(function(AerysRequest $req, Response $resp) use (&$kernel) {
  if (substr($req->getUri(), 0, 20) == '/sites/default/files') {
    return;
  }

  if (!in_array(pathinfo($req->getUri(), PATHINFO_EXTENSION), ['', 'php', 'inc', 'module'])) {
    return;
  }

  // Check whether drupal is installed and redirect of not.
  if (!Database::getConnectionInfo() && !drupal_installation_attempted() && PHP_SAPI !== 'cli') {
    $resp->end('Drupal Cannot be Installed Through Draerys at this time.');
    return;
  }

  // Try and move out of handler.
  $kernel->boot();
  $buffered_content = yield $req->getBody();
  $parsed_body = yield Aerys\parseBody($req);
  $post = $parsed_body->getAll();
  $request = Request::createFromAerysRequest($req, $post['fields'] ? $post['fields'] : [], $buffered_content);

  // Build the symfony response from the request.
  $result = $kernel->handle($request);

  // Pop the request we just added off of the request stack.
  // @todo: This is a work around. To do non-blocking Drupal properly we need
  // to find a way not to rely on a static current path service/request stack.
  $request_stack = $kernel->getContainer()->get('request_stack');
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
  $kernel->terminate($request, $result);
});
$host->use(root(__DIR__, ['mimeTypes' => ['svg' => 'image/svg+xml']]));
