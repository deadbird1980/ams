<?php
require_once 'BaseController.php';

class ErrorController extends BaseController {

	function userDefaultError() {
		$this->data['title'] = 'Member is not allowed!';
		$this->data['content'] = 'Not allowed';
		$this->data['printr'] = 'Access denied!';
		$this->renderAction('template');
	}

	function userAdminDeny() {
		switch($this->params['error']){
			case 'notAdmin':
				$error = 'You are not admin!';
				break;
			default:
				$error = 'Not allowed';
				break;
		}

		$this->data['title'] = 'Member is not allowed!';
		$this->data['content'] = $error;
		$this->data['printr'] = 'Access denied!';
		$this->renderAction('template');
	}

	function adminUserDeny() {
		$this->data['title'] = 'Admin is not allowed!';
		$this->data['content'] = ($this->params['error']=='vipOnly') ? 'This is VIP only!' : 'Not allowed';
		$this->data['printr'] = 'Access denied!';
		$this->renderAction('template');
	}

	function error404() {
		$this->data['title'] = 'Page not found!';
		$this->data['content'] = 'default 404 error';
		$this->data['printr'] = 'Nothing is found...';
		$this->renderAction('template');
	}

	function loginRequire() {
		$this->data['title'] = 'Login Required!';
		$this->data['content'] = 'You cannot access this!';
		$this->data['printr'] = 'You have to be logined to access this section.';
		$this->renderAction('template');
	}

}
?>
