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
if (isset($_ENV['ACCESS_USER'])) {
    $route['*']['/'] = array('AccountController', 'index', 'authName'=>'Blog Admin', 'auth'=>array($_ENV['ACCESS_USER']=>$_ENV['ACCESS_PASS']), 'authFailURL'=>'./error/loginFail');
}
$route['get']['/captcha/:file'] = array('AccountController', 'captcha');
$route['*']['/registration'] = array('AccountController', 'registration');
$route['*']['/forgotten_password'] = array('AccountController', 'forgottenPassword');
$route['post']['/register'] = array('AccountController', 'register');

$route['post']['/login'] = array('AccountController', 'login');
$route['*']['/logout'] = array('AccountController', 'logout');

//Admin pages
$route['*']['/admin'] = array('AdminController', 'home');
$route['*']['/admin/applications'] = array('ApplicationController', 'index');
$route['*']['/admin/applications/:id'] = array('AdminController', 'editApplication');
$route['*']['/admin/applications/:id/files'] = array('AdminController', 'uploadFiles');
$route['*']['/admin/files'] = array('FileController', 'index');
$route['*']['/admin/emails'] = array('EmailController', 'index');

//User pages
$route['*']['/admin/users'] = array('UserController', 'index');
$route['*']['/admin/users/:id'] = array('UserController', 'edit');
$route['*']['/users/:id'] = array('UserController', 'edit');
$route['*']['/users/:code/active'] = array('UserController', 'active');
$route['post']['/admin/users/:id'] = array('UserController', 'update');
$route['*']['/admin/users/create'] = array('UserController', 'create');
$route['post']['/admin/users/save'] = array('UserController', 'save');

//-------------
//-- my page
//-------------
$route['*']['/my'] = array('MyController', 'home');
$route['*']['/my/profile'] = array('MyController', 'profile');
$route['*']['/my/applications'] = array('MyController', 'listApplications');
$route['*']['/my/applications/page/:pindex'] = array('MyController', 'listApplications');
$route['*']['/my/applications/create/:type'] = array('MyController', 'apply');
$route['*']['/my/applications/:id'] = array('MyController', 'editApplication');
$route['*']['/my/applications/:id/files'] = array('MyController', 'uploadFiles');
$route['*']['/my/applications/:id/files/upload'] = array('MyController', 'uploadFile');
$route['*']['/my/applications/:id/confirm'] = array('MyController', 'confirmApplication');
$route['*']['/my/applications/:id/status'] = array('ApplicationController', 'status');
$route['*']['/my/applications/:id/submit'] = array('MyController', 'submitApplication');
$route['*']['/apply/visa/:type'] = array('MyController', 'applyVisa');
// admin/counselor/executor
$route['get']['/my/users'] = array('UserController', 'index');
$route['*']['/my/users/:id'] = array('UserController', 'edit');
$route['*']['/my/users/:user_id/applications'] = array('ApplicationController', 'index');
$route['*']['/my/users/:user_id/applications/create'] = array('ApplicationController', 'create');
$route['*']['/my/users/activate'] = array('UserController', 'activate');
$route['*']['/admin/users/:user_id/applications'] = array('ApplicationController', 'index');
$route['*']['/admin/users/:user_id/applications/create'] = array('ApplicationController', 'create');

$route['*']['/admin/users/page/:pindex'] = array('AdminController', 'home');

//files
$route['get']['/files'] = array('FileController', 'view');
$route['post']['/files'] = array('FileController', 'upload');

//Error
$route['*']['/error/user'] = array('ErrorController', 'userDefaultError');
$route['*']['/error/user/admin/:error'] = array('ErrorController', 'userAdminDeny');

$route['*']['/error/admin/sns/:error'] = array('ErrorController', 'adminSnsDeny');


$route['*']['/error/loginfirst'] = array('ErrorController', 'loginRequire');
$route['*']['/error'] = array('ErrorController', 'error404');


?>
