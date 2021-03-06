<?php
/* 
 * Common configuration that can be used throughout the application
 * Access via Singleton, eg. Doo::conf()->BASE_PATH;
 */
error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('Europe/London');

/**
 * for benchmark purpose, call Doo::benchmark() for time used.
 */
//$config['START_TIME'] = microtime(true);

$config['SITE_ID'] = 'b42dbf13b55235ca5e4d0042f015da38';

//framework use, must defined, user full absolute path and end with / eg. /var/www/project/
$config['SITE_PATH'] = realpath('..').'/app/';
//$config['PROTECTED_FOLDER'] = 'protected/';
$config['BASE_PATH'] = realpath('..').'/dooframework/';
$config['TMP_PATH'] = realpath('..').'/tmp/';
$config['UPLOAD_PATH'] = realpath('..').'/app/uploads/';


//for production mode use 'prod'
$config['APP_MODE'] = 'dev';
//----------------- optional, if not defined, default settings are optimized for production mode ----------------
//if your root directory is /var/www/ and you place this in a subfolder eg. 'app', define SUBFOLDER = '/app/'

if (strpos(str_replace('\\','/',$config['SITE_PATH']), $_SERVER['DOCUMENT_ROOT']) !== false) {
    $config['SUBFOLDER'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\','/',$config['SITE_PATH']));
} else {
    $config['SUBFOLDER'] = str_replace('index.php', '', $_SERVER['REQUEST_URI']);
}
if(strpos($config['SUBFOLDER'], '/')!==0){
	$config['SUBFOLDER'] = '/'.$config['SUBFOLDER'];
}

$config['APP_URL'] = 'http://'.$_SERVER['HTTP_HOST'].$config['SUBFOLDER'];
$config['AUTOROUTE'] = TRUE;
$config['ENTRY_INDEX'] = 'index.php';
$config['DEBUG_ENABLED'] = FALSE;

$config['TEMPLATE_COMPILE_ALWAYS'] = TRUE;

//register functions to be used with your template files
//$config['TEMPLATE_GLOBAL_TAGS'] = array('url', 'url2', 'time', 'isset', 'empty');

/**
 * Path to store logs/profiles when using with the logger tool. This is needed for writing log files and using the log viewer tool
 */
//$config['LOG_PATH'] = '/var/logs/';


/**
 * defined either Document or Route to be loaded/executed when requested page is not found
 * A 404 route must be one of the routes defined in routes.conf.php (if autoroute on, make sure the controller and method exist)
 * Error document must be more than 512 bytes as IE sees it as a normal 404 sent if < 512b
 */
//$config['ERROR_404_DOCUMENT'] = 'error.php';
$config['ERROR_404_ROUTE'] = '/error';

$config['lang'] = 'zh';
$config['default_lang'] = 'en';

/**
 * Settings for memcache server connections, you don't have to set if using localhost only.
 * host, port, persistent, weight
 * $config['MEMCACHE'] = array(
 *                       array('192.168.1.31', '11211', true, 40),
 *                       array('192.168.1.23', '11211', true, 80)
 *                     );
 */

/**
 * you can include self defined config, retrieved via Doo::conf()->variable
 * Use lower case for you own settings for future Compability with DooPHP
 */
$config['pagesize'] = 10;
$config['pages'] = 10;
$config['support_email'] = 'support@mmxcode.com';
$config['error_email'] = 'error@mmxcode.com';

?>
