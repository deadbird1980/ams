<?php

// anonymous user can only access Account index page.
$acl['anonymous']['allow'] = array(
							'AccountController'=>array('*')
						);

$acl['user']['allow'] = array(
							'MyController'=>'*', 
						);

$acl['user']['deny'] = array(
							'AdminController'=>array('*'), 
							'UserController' =>array('*')
						);


$acl['admin']['allow'] = '*';
$acl['admin']['deny'] = array(
							'SnsController'=>array('showVipHome')
						);

$acl['user']['failRoute'] = array(
								'_default'=>'/error/user',	//if not found this will be used
								'AdminController/banUser'=>'/error/user/notAdmin', 
							);

$acl['anonymous']['failRoute'] = array(
								'_default'=>'/error/loginfirst',
							);
?>
