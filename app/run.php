<?php
include './protected/config/common.conf.php';
include './protected/config/routes_cli.conf.php';
include './protected/config/db.conf.php';

#Just include this for production mode
//include $config['BASE_PATH'].'deployment/deploy.php';
include $config['BASE_PATH'].'Doo.php';
include $config['BASE_PATH'].'app/DooConfig.php';

Doo::conf()->set($config);

Doo::db()->setMap($dbmap);
Doo::db()->setDb($dbconfig, $config['APP_MODE']);
Doo::db()->sql_tracking = true;

Doo::app('DooCliApp')->route = $route;

Doo::app()->run($_SERVER['argv']);
?>
