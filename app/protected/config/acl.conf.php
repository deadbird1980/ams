<?php

// anonymous user can only access Account index page.
$acl['anonymous']['allow'] = array(
							'AccountController'=>'*'
						);

// customer/counselor/executor/admin

$acl['customer']['allow'] = array(
							'MyController'=>'*',
							'AccountController'=>'*',
							'ApplicationController'=>array('confirm'),
						);
$acl['counselor']['allow'] = array(
							'MyController'=>'*',
							'AccountController'=>'*',
							'UserController'=>'*',
							'ApplicationController'=>'*',
							'CourseController'=>'*',
						);
$acl['executor']['allow'] = array(
							'MyController'=>'*',
							'AccountController'=>'*',
							'UserController'=>'*',
							'CourseController'=>'*',
							'ApplicationController'=>'*'
						);

$acl['customer']['deny'] = array(
							'AdminController'=>array('*'), 
							'UserController' =>array('*')
						);
$acl['counselor']['deny'] = array(
							'AdminController'=>array('*'),
						);
$acl['executor']['deny'] = array(
							'AdminController'=>array('*'),
						);


$acl['admin']['allow'] = '*';

$acl['customer']['failRoute'] = array(
								'_default'=>'/error/user',	//if not found this will be used
								'AdminController/banUser'=>'/error/user/notAdmin', 
							);

$acl['anonymous']['failRoute'] = array(
								'_default'=>'/error/loginfirst',
							);
?>
