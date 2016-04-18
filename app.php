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
  $request = Request::createFromAerysRequest($req);
  $result = $kernel->handle($request);

  // Write all headers out.
  foreach ($result->headers->allPreserveCase() as $key => $value) {
    $resp->setHeader($key, $value);
    $resp->end($result->getContent());
  }
});
$host->use(root(__DIR__. "/sites/default/files"));
