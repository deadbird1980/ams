<?php

class UserController extends DooController {
	
	public function beforeRun($resource, $action){
		session_start();
		
		//if not login, group = anonymous
		$role = (isset($_SESSION['user']['group'])) ? $_SESSION['user']['group'] : 'anonymous';
		
		if($role!='anonymous'){
				$role = 'admin';
		}
		
		//check against the ACL rules
		if($rs = $this->acl()->process($role, $resource, $action )){
			//echo $role .' is not allowed for '. $resource . ' '. $action;
			return $rs;
		}
	}

    function index() {
		$data['baseurl'] = Doo::conf()->APP_URL;
        Doo::loadModel('User');
        $user = new User();
        $data['users'] = $user->find();
		$this->render('user_list', $data);
    }

	function create() {
		$data['baseurl'] = Doo::conf()->APP_URL;
		$data['title'] = 'new User';
		$this->render('user', $data);
	}

	function viewProfile() {
		$data['baseurl'] = Doo::conf()->APP_URL;
		$data['title'] = 'Profile of ' . $this->params['uname'];
		$data['content'] = 'You can access this~';
		$data['printr'] = 'Hi I am '. $this->params['uname'] . ' and I am a cool guy.';
		$this->render('template', $data);
	}

	function banUser() {
		$data['baseurl'] = Doo::conf()->APP_URL;
		$data['title'] = 'Banning User';
		$data['content'] = 'You can access this~';
		$data['printr'] = '<input type="button" value="Ban this user?" />';
		$this->render('template', $data);
	}

}
?>
