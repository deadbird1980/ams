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
        $u->id = $this->session->user['id'];
        $this->user = $this->db()->find($u, array('limit'=>1));
        $this->sortField = '';
        $this->orderType = '';
	}

	public function home() {
        $this->renderAction('/my/'.$this->session->user['type'].'/index');
	}

	public function listUsers() {
        Doo::loadHelper('DooPager');
        $user = $this->user;
        $u = new User;
        //if default, no sorting defined by user, show this as pager link
        if($this->sortField=='email' && $this->orderType=='desc'){
            $pager = new DooPager(Doo::conf()->APP_URL.'admin/user/page', $u->count($user->scopeSeenByMe()), 6, 10);
        }else{
            $pager = new DooPager(Doo::conf()->APP_URL."admin/user/sort/$this->sortField/$this->orderType/page", $u->count($user->scopeSeenByMe()), 6, 10);
        }

        if(isset($this->params['pindex']))
            $pager->paginate(intval($this->params['pindex']));
        else
            $pager->paginate(1);

        $this->data['pager'] = $pager->output;

        $columns = 'id,email,first_name,last_name,first_name_alphabet,last_name_alphabet,phone,qq,status';
        //Order by ASC or DESC
        if($this->orderType=='desc'){
            $this->data['users'] = $u->limit($pager->limit, null, $this->sortField,
                                        array_merge(array('select'=>$columns), $user->scopeSeenByMe())
                                  );
            $this->data['order'] = 'asc';
        }else{
            $this->data['users'] = $u->limit($pager->limit, $this->sortField, null,
                                        //we don't want to select the Content (waste of resources)
                                        array_merge(array('select'=>$columns), $user->scopeSeenByMe())
                                  );
            $this->data['order'] = 'desc';
        }
        $form = $this->getActivateUserForm();
        $this->data['form'] = $form->render();
        $this->renderAction('/my/'.$this->session->user['type'].'/users');
	}

    public function profile() {
		$this->data['title'] = 'User';
        Doo::loadModel('User');

        $u = new User();
        $u->id = $this->params['id'];
        $user = $this->db()->find($u, array('limit'=>1));
		$this->data['user'] = $user;
		$this->renderAction('my_profile');
    }

    public function activateUser() {
        $form = $this->getActivateUserForm();
        if ($this->isPost() && $form->isValid($_POST)) {
            $u = new User();
            $u->findByConfirm_code($_POST['confirm_code']);
            $u->activate($this->user);
            $this->data['message'] = "User activated!";
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/user/activate');
    }

    public function applyVisa() {
    }

    private function getActivateUserForm() {
        Doo::loadHelper('DooForm');
        $action = Doo::conf()->APP_URL . 'index.php/my/users/activate';
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements' => array(
                 'confirm_code' => array('text', array(
                     'validators' => array(array('dbExist', 'User', 'confirm_code', 'The confirm code does not exist!')),
                     'label' => 'Confirm Code:',
                     'required' => true,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'submit' => array('submit', array(
                     'label' => "激活",
                     'attributes' => array('class' => 'buttons'),
                     'order' => 100,
                 'field-wrapper' => 'div'
                 ))
             )
        ));
        return $form;
    }

}
?>
