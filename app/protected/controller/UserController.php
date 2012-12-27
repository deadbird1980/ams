<?php
require_once 'AdminController.php';

class UserController extends AdminController {
	public function beforeRun($resource, $action){
        parent::beforeRun($resource, $action);
        $this->setUser();
    }

    public function index() {
        Doo::loadHelper('DooPager');
        Doo::loadModel('User');

        $u = new User();
        //if default, no sorting defined by user, show this as pager link
        if($this->sortField=='email' && $this->orderType=='desc'){
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
                                        array('select'=>'id,email,first_name,last_name')
                                  );
            $data['order'] = 'asc';
        }else{
            $data['users'] = $u->limit($pager->limit, $this->sortField, null,
                                        //we don't want to select the Content (waste of resources)
                                        array('select'=>'id,email,first_name,last_name')
                                  );
            $data['order'] = 'desc';
        }

        $this->render('admin', $data);
    }

	public function create() {
		$data['baseurl'] = Doo::conf()->APP_URL;
		$data['title'] = 'new User';
		$this->render('user', $data);
	}

	public function edit() {
		$this->data['title'] = 'User';
        $form = $this->getUserForm();
        $this->data['form'] = $form->render();
		$this->renderAction('admin_user');
	}

	public function update() {
		$this->data['title'] = 'User';
        $form = $this->getUserForm();
        if ($form->isValid($_POST)) {
            $u = $this->data['user'];
            $u->first_name = $_POST['first_name'];
            $u->last_name = $_POST['last_name'];
            $u->email = $_POST['email'];
            $u->password = $_POST['password'];
            $u->qq = $_POST['qq'];
            $u->confirm_code = $_POST['confirm_code'];
            $u->type = $_POST['type'];
            $u->update(array('where'=>"id={$u->id}",'field'=>'email,type,first_name,last_name,password,qq,confirm_code'));
            $this->data['message'] = 'updated';
            $form = $this->getUserForm();
        }
        $this->data['form'] = $form->render();
		$this->renderAction('admin_user');
	}

    private function setUser() {
        if (isset($this->params['id'])) {
            Doo::loadModel('User');
            $u = new User;
            $u->id = $this->params['id'];
            $user = $this->db()->find($u, array('limit'=>1));
            $this->data['user'] = $user;
        }
    }

    private function getUserForm() {
        Doo::loadHelper('DooForm');
        $u = $this->data['user'];
        $action = Doo::conf()->APP_URL . 'index.php/admin/users/'.$u->id;
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements' => array(
                 'first_name' => array('text', array(
                     'required' => true,
                     'label' => 'First Name:',
                     'value' => $u->first_name,
                     'attributes' => array('class' => 'control first_name validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'last_name' => array('text', array(
                     'required' => true,
                     'label' => 'Last Name:',
                     'value' => $u->last_name,
                     'attributes' => array('class' => 'control last_name validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'password' => array('password', array(
                     'required' => true,
                     'validators' => array('password'),
                     'label' => 'Password:',
                     'value' => $u->password,
                 'attributes' => array('class' => 'control password validate[required,length(6,10)]'),
                 'element-wrapper' => 'div'
                 )),
                 'email' => array('text', array(
                     'required' => true,
                     'validators' => array(array('email')),
                     'label' => 'Email:',
                     'value' => $u->email,
                     'attributes' => array('class' => 'control email validate[required,email]'),
                 'element-wrapper' => 'div'
                 )),
                 'qq' => array('text', array(
                     'required' => true,
                     'label' => 'QQ:',
                     'value' => $u->qq,
                     'attributes' => array('class' => 'control qq validate[required,email]'),
                 'element-wrapper' => 'div'
                 )),
                 'confirm_code' => array('text', array(
                     'required' => true,
                     'label' => 'Confirm Code:',
                     'value' => $u->confirm_code,
                     'attributes' => array('class' => 'control confirm_code'),
                     'element-wrapper' => 'div'
                 )),
                 'type' => array('select', array(
                     'required' => true,
                     'multioptions' => array('' => '' , 'admin' => 'admin', 'staff' => 'staff', 'customer' => 'customer'),
                     'label' => 'Type:',
                     'value' => $u->type,
                     'attributes' => array('class' => 'control type'),
                     'element-wrapper' => 'div'
                 )),
                 'submit' => array('submit', array(
                     'label' => "Save",
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
