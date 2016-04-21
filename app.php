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
use Draerys\DraerysResponder;
use Draerys\Http\Request;
use Drupal\Core\Database\Database;

$autoloader = require 'vendor/autoload.php';

$site_path = 'sites/default';

$kernel = new DraerysKernel('prod', $autoloader);
$kernel->bootEnvironment();
$kernel->setSitePath($site_path);

$host = new Host();
$host->use((new DraerysResponder($kernel)));
$host->use(root(__DIR__, ['mimeTypes' => ['svg' => 'image/svg+xml']]));
