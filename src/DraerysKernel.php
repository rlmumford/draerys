<?php
/**
 * @file
 * Contains \Draerys\DraerysKernel
 */

namespace Draerys;

use Drupal\Core\DrupalKernel;

class DraerysKernel extends DrupalKernel {

  /**
   * {@inheritdoc}
   */
  public function __construct($environment, $class_loader, $allow_dumping = TRUE) {
    $this->containerNeedsRebuild = TRUE;
    parent::__construct($environment, $class_loader, $allow_dumping);
    $this->root = dirname(dirname(__FILE__));
  }

  /**
   * {@inheritdoc}
   */
  public function setSitePath($path) {
    // Only do this once per server. Potentially we should set this up when the
    // server is initialised.
    if (!$this->booted) {
      $this->sitePath = $path;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function discoverServiceProviders() {
    parent::discoverServiceProviders();
    $this->serviceYamls['app']['core'] = $this->root . '/core/core.services.yml';
  }
}