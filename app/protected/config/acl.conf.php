<?php

// anonymous user can only access Account index page.
$acl['anonymous']['allow'] = array(
							'AccountController'=>array('*')
						);

// customer/counselor/executor/admin

$acl['customer']['allow'] = array(
							'MyController'=>'*', 
						);

$acl['customer']['deny'] = array(
							'AdminController'=>array('*'), 
							'UserController' =>array('*')
						);


$acl['admin']['allow'] = '*';

$acl['user']['failRoute'] = array(
								'_default'=>'/error/user',	//if not found this will be used
								'AdminController/banUser'=>'/error/user/notAdmin', 
							);

$acl['anonymous']['failRoute'] = array(
								'_default'=>'/error/loginfirst',
							);
?>
