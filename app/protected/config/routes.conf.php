<?php
/**
 * Define your URI routes here.
 *
 * $route[Request Method][Uri] = array( Controller class, action method, other options, etc. )
 *
 * RESTful api support, *=any request method, GET PUT POST DELETE
 * POST 	Create
 * GET      Read
 * PUT      Update, Create
 * DELETE 	Delete
 *
 * Use lowercase for Request Method
 *
 * If you have your controller file name different from its class name, eg. home.php HomeController
 * $route['*']['/'] = array('HomeController', 'index', 'className'=>'HomeController');
 */

$route['*']['/'] = array('AccountController', 'index');
$route['*']['/registration'] = array('AccountController', 'registration');
$route['post']['/register'] = array('AccountController', 'register');

$route['post']['/login'] = array('AccountController', 'login');
$route['*']['/logout'] = array('AccountController', 'logout');

//Admin pages
$route['*']['/admin'] = array('AdminController', 'home');

//User pages
$route['*']['/admin/users'] = array('UserController', 'index');
$route['*']['/admin/users/:id'] = array('UserController', 'edit');
$route['post']['/admin/users/:id'] = array('UserController', 'update');
$route['*']['/admin/users/create'] = array('UserController', 'create');

//Error
$route['*']['/error/user'] = array('ErrorController', 'userDefaultError');
$route['*']['/error/user/admin/:error'] = array('ErrorController', 'userAdminDeny');

$route['*']['/error/admin/sns/:error'] = array('ErrorController', 'adminSnsDeny');


$route['*']['/error/loginfirst'] = array('ErrorController', 'loginRequire');
$route['*']['/error'] = array('ErrorController', 'error404');


?>
