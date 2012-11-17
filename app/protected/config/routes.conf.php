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

//User pages
$route['*']['/users'] = array('UserController', 'index');
$route['*']['/users/:username'] = array('UserController', 'edit');
$route['*']['/users/create'] = array('UserController', 'create');

//Blog
$route['*']['/sns/blog'] = array('BlogController', 'index');
$route['*']['/sns/blog/comments'] = array('BlogController', 'comments');
$route['*']['/sns/blog/comments/delete'] = array('BlogController', 'deleteComment');
$route['*']['/sns/blog/write'] = array('BlogController', 'writePost');

//Sns
$route['*']['/sns/games'] = array('SnsController', 'game');
$route['*']['/sns/people/:uname'] = array('SnsController', 'viewProfile');
$route['*']['/sns/ban'] = array('SnsController', 'banUser');

//Error
$route['*']['/error/member'] = array('ErrorController', 'memberDefaultError');
$route['*']['/error/member/sns/:error'] = array('ErrorController', 'memberSnsDeny');
$route['*']['/error/member/blog/:error'] = array('ErrorController', 'memberBlogDeny');

$route['*']['/error/admin/sns/:error'] = array('ErrorController', 'adminSnsDeny');


$route['*']['/error/loginfirst'] = array('ErrorController', 'loginRequire');
$route['*']['/error'] = array('ErrorController', 'error404');


?>
