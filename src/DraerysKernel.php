<?php
/**
 * @file
 * Contains \Draerys\DraerysKernel
 */

namespace Draerys;

use Drupal\Core\DrupalKernel;
use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DraerysKernel extends DrupalKernel {

  /**
   * {@inheritdoc}
   */
  public function __construct($environment, $class_loader, $allow_dumping = TRUE) {
    $this->containerNeedsRebuild = TRUE;
    parent::__construct($environment, $class_loader, $allow_dumping);
  }

  /**
   * {@inheritdoc}
   */
  public function setSitePath($path) {
    // Only do this once per server. Potentially we should set this up when the
    // server is initialised.
    if (!$this->booted) {
      $this->sitePath = $path;
      $this->initializeSiteSettings($path);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function discoverServiceProviders() {
    parent::discoverServiceProviders();
    $this->serviceYamls['app']['core'] = $this->root . '/core/core.services.yml';
  }

  /**
   * {@inheritdoc}
   */
  protected function initializeSiteSettings($site_path) {
    $class_loader_class = get_class($this->classLoader);
    Settings::initialize($this->root, $site_path, $this->classLoader);

    // Initialize our list of trusted HTTP Host headers to protect against
    // header attacks.
    $host_patterns = Settings::get('trusted_host_patterns', array());
    if (PHP_SAPI !== 'cli' && !empty($host_patterns)) {
      if (static::setupTrustedHosts($request, $host_patterns) === FALSE) {
        throw new BadRequestHttpException('The provided host name is not valid for this server.');
      }
    }

    // If the class loader is still the same, possibly upgrade to the APC class
    // loader.
    // ApcClassLoader does not support APCu without backwards compatibility
    // enabled.
    if ($class_loader_class == get_class($this->classLoader)
     && Settings::get('class_loader_auto_detect', TRUE)
       && extension_loaded('apc')) {
      $prefix = Settings::getApcuPrefix('class_loader', $this->root);
      $apc_loader = new \Symfony\Component\ClassLoader\ApcClassLoader($prefix, $this->classLoader);
      $this->classLoader->unregister();
      $apc_loader->register();
      $this->classLoader = $apc_loader;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = TRUE) {
    try {
      $response = $this->getHttpKernel()->handle($request, $type, $catch);
    }
    catch (\Exception $e) {
      if ($catch === FALSE) {
        throw $e;
      }
      $response = $this->handleException($e, $request, $type);
    }

    // Adapt response headers to the current request.
    $response->prepare($request);
    return $response;
  }
}