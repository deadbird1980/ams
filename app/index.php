<?php
include './protected/config/common.conf.php';
include './protected/config/routes.conf.php';
include './protected/config/db.conf.php';
include './protected/config/acl.conf.php';

#Just include this for production mode
//include $config['BASE_PATH'].'deployment/deploy.php';
include $config['BASE_PATH'].'Doo.php';
include $config['BASE_PATH'].'app/DooConfig.php';

Doo::conf()->set($config);
if (isset($config['DEBUG_ENABLED']) && $config['DEBUG_ENABLED']) {
    include $config['BASE_PATH'].'diagnostic/debug.php';
} else {
    // Our custom error handler  
    Doo::loadClass('ErrorHandler');
}

Doo::acl()->rules = $acl;
Doo::acl()->defaultFailedRoute = '/error';

Doo::db()->setMap($dbmap);
Doo::db()->setDb($dbconfig, $config['APP_MODE']);
//Doo::db()->sql_tracking = true;

Doo::app()->route = $route;

Doo::app()->run();
?>
