# Draerys - Drupal on Aerys

Draerys is a attempt at running drupal on an Aerys server, provided by the team at amphp.

## Installation

Checkout the master branch and run `composer install` to download all dependencies. You can then run the server by running `vendor/bin/aerys -d -c app.php`. This will expose the default site on localhost port 80.

**N.B.** Currently it is not possible to access the install pages through draerys. Try installing a site through drush first.

## Motivation

Currently this package is for experimenting with a persistant bootstrap for performance improvements. But is also allows us to experiment with websockets and non-blocking architectures in drupal 8.

## Known Issues

- Not Possible to install a new site with draerys.
