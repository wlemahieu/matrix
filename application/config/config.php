<?php

/**
 * Configuration
 *
 * For more info about constants please @see http://php.net/manual/en/function.define.php
 */

/* Start a session */
session_start();

/* Set the default PHP timezone */
date_default_timezone_set('America/Los_Angeles');

/**
 * Configuration for: Error reporting
 * Useful to show every little problem during development, but only show hard errors in production
 */
define('ENVIRONMENT', 'development');

if($_SERVER['HTTP_HOST'] == 'XXXXXXXXXX') {
	//define('ENVIRONMENT', 'development');
	//define('ENVIRONMENT', 'production');
} else {
	//define('ENVIRONMENT', 'production');
}

// turn on error reporting in development
if (ENVIRONMENT == 'development' || ENVIRONMENT == 'dev') {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

// define constants
define('URL_PUBLIC_FOLDER', 'public');
define('URL_PROTOCOL', 'http://');
define('URL_DOMAIN', $_SERVER['HTTP_HOST']);
define('URL_SUB_FOLDER', str_replace(URL_PUBLIC_FOLDER, '', dirname($_SERVER['SCRIPT_NAME'])));
define('URL', URL_PROTOCOL . URL_DOMAIN . URL_SUB_FOLDER);

require 'db-config.php';
require 'memcache-config.php';