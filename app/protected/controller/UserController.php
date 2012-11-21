<?php
require_once 'AdminController.php';

class UserController extends AdminController {

    function index() {
        Doo::loadHelper('DooPager');
        Doo::loadModel('User');

        $u = new User();
        //if default, no sorting defined by user, show this as pager link
        if($this->sortField=='username' && $this->orderType=='desc'){
            $pager = new DooPager(Doo::conf()->APP_URL.'admin/user/page', $u->count(), 6, 10);
        }else{
            $pager = new DooPager(Doo::conf()->APP_URL."admin/user/sort/$this->sortField/$this->orderType/page", $u->count(), 6, 10);
        }

        if(isset($this->params['pindex']))
            $pager->paginate(intval($this->params['pindex']));
        else
            $pager->paginate(1);

        $data['rootUrl'] = Doo::conf()->APP_URL;
        $data['baseurl'] = Doo::conf()->APP_URL;
        $data['pager'] = $pager->output;

        //Order by ASC or DESC
        if($this->orderType=='desc'){
            $data['users'] = $u->limit($pager->limit, null, $this->sortField,
                                        //we don't want to select the Content (waste of resources)
                                        array('select'=>'id,username,email,first_name,last_name')
                                  );
            $data['order'] = 'asc';
        }else{
            $data['users'] = $u->limit($pager->limit, $this->sortField, null,
                                        //we don't want to select the Content (waste of resources)
                                        array('select'=>'id,username,email,first_name,last_name')
                                  );
            $data['order'] = 'desc';
        }

        $this->render('admin', $data);
    }

	function create() {
		$data['baseurl'] = Doo::conf()->APP_URL;
		$data['title'] = 'new User';
		$this->render('user', $data);
	}

	function edit() {
		$this->data['title'] = 'User';
        Doo::loadModel('User');

        $u = new User();
        $u->id = $this->params['id'];
        $user = $this->db()->find($u, array('limit'=>1));
		$this->data['user'] = $user;
		$this->renderAction('admin_user');
	}

}
?>
