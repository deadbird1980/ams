<?php
require_once 'BaseController.php';

class MyController extends BaseController {

    protected $user;
	public function beforeRun($resource, $action){
        parent::beforeRun($resource, $action);

		//if not login, group = anonymous
		$role = (isset($this->session->user['type'])) ? $this->session->user['type'] : 'anonymous';

		if($role!='anonymous'){
				$role = 'student';
		}

		//check against the ACL rules
		if($rs = $this->acl()->process($role, $resource, $action )){
			//echo $role .' is not allowed for '. $resource . ' '. $action;
			//return $rs;
		}

        Doo::loadModel('User');

        $u = new User();
        $u->id = $this->params['id'];
        $this->user = $this->db()->find($u, array('limit'=>1));
	}

    /**
     * Display the list of paginated Posts (draft and published)
     */
	function home() {
        $this->renderAction('my');
	}

    function profile() {
		$this->data['title'] = 'User';
        Doo::loadModel('User');

        $u = new User();
        $u->id = $this->params['id'];
        $user = $this->db()->find($u, array('limit'=>1));
		$this->data['user'] = $user;
		$this->renderAction('my_profile');
    }

}
?>
