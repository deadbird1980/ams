<?php

/*
 * Set error reporting to the level to which ams code must comply.
 */
error_reporting( E_ALL | E_STRICT );

/*
 * Determine the root, library, and tests directories of the framework
 * distribution.
 */
$amsRoot        = realpath(dirname(__DIR__));
$amsApplication = "$amsRoot/app/protected";
$amsModels = "$amsApplication/model";
$amsControllers = "$amsApplication/controller";
$amsCoreTests   = "$amsRoot/tests";
$dooFramework   = "$amsRoot/dooframework";

$path = array(
    $dooFramework,
    $amsApplication,
    $amsModels,
    $amsControllers,
    get_include_path(),
);
set_include_path(implode(PATH_SEPARATOR, $path));

/**
 * Setup Environment
 */
include "$amsRoot/app/protected/config/common.conf.php";
include "$amsRoot/app/protected/config/routes.conf.php";
include "$amsRoot/app/protected/config/db.conf.php";
include "$amsRoot/app/protected/config/acl.conf.php";
include $config['BASE_PATH'].'Doo.php';
include $config['BASE_PATH'].'app/DooConfig.php';

Doo::conf()->set($config);
Doo::acl()->rules = $acl;
Doo::acl()->defaultFailedRoute = '/error';

Doo::db()->setMap($dbmap);
Doo::db()->setDb($dbconfig, $config['APP_MODE']);

/**
 * End
 */

if (defined('TESTS_GENERATE_REPORT') && TESTS_GENERATE_REPORT === true) {
    $codeCoverageFilter = new PHP_CodeCoverage_Filter();

    $lastArg = end($_SERVER['argv']);
    if (is_dir($amsCoreTests . '/' . $lastArg)) {
        $codeCoverageFilter->addDirectoryToWhitelist($amsModels . '/' . $lastArg);
        $codeCoverageFilter->addDirectoryToWhitelist($amsControllers . '/' . $lastArg);
    } elseif (is_file($amsCoreTests . '/' . $lastArg)) {
        $codeCoverageFilter->addDirectoryToWhitelist(dirname($amsModels . '/' . $lastArg));
    } else {
        $codeCoverageFilter->addDirectoryToWhitelist($zfCoreLibrary);
    }

    /*
     * Omit from code coverage reports the contents of the tests directory
     */
    $codeCoverageFilter->addDirectoryToBlacklist($amsCoreTests, '');
    $codeCoverageFilter->addDirectoryToBlacklist(PEAR_INSTALL_DIR, '');
    $codeCoverageFilter->addDirectoryToBlacklist(PHP_LIBDIR, '');

    unset($codeCoverageFilter);
}


/**
 * Start output buffering, if enabled
 */
if (defined('TESTS_AMS_OB_ENABLED') && constant('TESTS_AMS_OB_ENABLED')) {
    ob_start();
}

/*
 * Unset global variables that are no longer needed.
 */
unset($amsRoot, $amsApplication, $amsModels, $amsControllers, $amsCoreTests);
