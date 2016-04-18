<?php
/**
 * @file
 * Drearys application file.
 */
 
use Aerys\function router;
use Aerys\Host;
use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

$autoload = require_once 'vendor/autoload.php';
$kernel = new DrupalKernel('prod', $autoload);

$host = new Host();
$host->use();
 