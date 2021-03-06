<?php

/*-------------------------------------------------------+
 | KDE Commit-Digest
 | Copyright 2010-2013 Danny Allen <danny@commit-digest.org>
 | http://www.commit-digest.org/
 +--------------------------------------------------------+
 | This program is released as free software under the
 | Affero GPL license. You can redistribute it and/or
 | modify it under the terms of this license which you
 | can read by viewing the included agpl.txt or online
 | at www.gnu.org/licenses/agpl.html. Removal of this
 | copyright header is strictly prohibited without
 | written permission from the original author(s).
 +--------------------------------------------------------*/


// set initial values
if (empty($_SERVER['DOCUMENT_ROOT'])) {
  define('COMMAND_LINE',      true);
  define('BASE_DIR',          dirname(__FILE__));

} else {
  define('BASE_DIR',          rtrim($_SERVER['DOCUMENT_ROOT'], '/'));
  define('COMMAND_LINE',      false);
}


if (COMMAND_LINE) {
  // set command line vars (error reporting, etc)
  error_reporting(E_ALL);
  define('LIVE_SITE', null);

} else {
  // set general site vars
  error_reporting(E_ALL|E_STRICT);

  // set protocol
  if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) {
    define('PROTOCOL',      'https://');
  } else {
    define('PROTOCOL',      'http://');
  }

  define('BASE_URL',        PROTOCOL . $_SERVER['HTTP_HOST']);
  define('BASE_URL_HTTP',   'http://' . $_SERVER['HTTP_HOST']);
  define('BASE_URL_HTTPS',  'https://' . $_SERVER['HTTP_HOST']);

  // start user session
  session_start();

  // set environment (live / development)
  if (($_SERVER['HTTP_HOST'] == 'digest') ||
      ($_SERVER['HTTP_HOST'] == 'localhost')) {

    define('LIVE_SITE', false);

  } else {
    define('LIVE_SITE', true);
  }
}


// set live site vars
if (LIVE_SITE) {
  define('MINIFIED', '.min');

  // error handling (log to file)
  ini_set('display_errors', false);
  ini_set('log_errors',     true);

  // webstats
  define('WEBSTATS_TYPE',   'google');
  define('WEBSTATS_ID',     'UA-46299859-1');

} else {
  define('MINIFIED', '');

  // error handling (show on page)
  ini_set('display_errors', true);
  ini_set('log_errors',     false);

  // webstats
  define('WEBSTATS_TYPE',   false);
}


if (COMMAND_LINE) {
  ini_set('display_errors', true);

  // autoload doesn't work...
  function autoload($classes) {
    if (!is_array($classes)) {
      $classes = array($classes);
    }

    foreach ($classes as $class) {
      include('classes/' . $class . '.php');
    }
  }

} else {
  // add class dir to include path
  $classDirs = array(
    BASE_DIR . '/classes/db/',
    BASE_DIR . '/classes/shared/',
    BASE_DIR . '/classes/specific/',
    BASE_DIR . '/classes/ext/',
    BASE_DIR . '/classes/ext/cacheLite/'
  );

  set_include_path(get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR, $classDirs));

  // define autoloader
  spl_autoload_register();
}

// conditionally adjust the classdir to make enzyme themable
if (isset(Config::$theme) && (Config::$theme[0] !== 'default')) {
  $classDirs[] = BASE_DIR . '/classes/ui/themes/' . Config::$theme[0] . '/';
} else {
  $classDirs[] = BASE_DIR . '/classes/ui/';
}

// rerun the autoloader
set_include_path(get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR, $classDirs));
spl_autoload_register();


// import templating engine
require_once BASE_DIR . '/classes/ext/Twig/Autoloader.php';
Twig_Autoloader::register();


// make APP_ID's consistently available
define('DIGEST_APP_ID',       Config::$app['id']);
define('ENZYME_APP_ID',       'enzyme');

define('JAVASCRIPT_LIBRARY',  'jquery');


// stop APC cache slam errors
ini_set('apc.slam_defense', 'Off');


// define caching settings
define('CACHE_DIR', BASE_DIR . '/cache/');

$cacheOptions = array('caching'             => false,
                      'cacheDir'            => CACHE_DIR,
                      'lifetime'            => 3600,
                      'fileNameProtection'  => true,
                      'writeControl'        => true,
                      'readControl'         => false,
                      'readControlType'     => 'md5');


// connect to database
Db::connect();


// load settings
$settings = Enzyme::loadSettings(true);

if (!$settings) {
  // show message about configuration
  echo Ui::drawHtmlPage(_('Enzyme backend instance needs to be configured.'),
                        _('Setup'),
                        array('/css/includes/common' . MINIFIED . '.css'));
  exit;
}


// set language
App::setLanguage();

// set timezone
date_default_timezone_set(Config::getSetting('locale', 'TIMEZONE'));

?>