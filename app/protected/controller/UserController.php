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

        $this->data['pager'] = $pager->output;

        $columns = 'id,email,first_name,last_name,first_name_alphabet,last_name_alphabet,phone,qq,status';
        //Order by ASC or DESC
        if($this->orderType=='desc'){
            $this->data['users'] = $u->limit($pager->limit, null, $this->sortField,
                                        //we don't want to select the Content (waste of resources)
                                        array('select'=>$columns)
                                  );
            $this->data['order'] = 'asc';
        }else{
            $this->data['users'] = $u->limit($pager->limit, $this->sortField, null,
                                        //we don't want to select the Content (waste of resources)
                                        array('select'=>$columns)
                                  );
            $this->data['order'] = 'desc';
        }

        $this->renderAction('/admin/user/index');
    }

	public function save() {
		$this->data['title'] = 'new User';
		$this->data['message'] = '';
        $this->data['user'] = new User();
        $form = $this->getUserForm();
        if ($form->isValid($_POST)) {
            $u = $this->data['user'];
            $u->first_name = $_POST['first_name'];
            $u->last_name = $_POST['last_name'];
            $u->first_name_alphabet = $_POST['first_name_alphabet'];
            $u->last_name_alphabet = $_POST['last_name_alphabet'];
            $u->email = $_POST['email'];
            $u->password = $_POST['password'];
            $u->qq = $_POST['qq'];
            $u->confirm_code = $_POST['confirm_code'];
            $u->type = $_POST['type'];
            $u->insert();
            $form = $this->getUserForm();
        }
        $this->data['form'] = $form->render();
		$this->renderAction('/admin/user/edit');
	}

	public function create() {
		$this->data['title'] = 'new User';
		$this->data['message'] = '';
        $this->data['user'] = new User;
        $form = $this->getUserForm();
        $this->data['form'] = $form->render();
		$this->renderAction('/admin/user/edit');
	}

	public function edit() {
		$this->data['title'] = 'User';
        $form = $this->getUserForm();
        $this->data['form'] = $form->render();
		$this->renderAction('/admin/user/edit');
	}

	public function update() {
		$this->data['title'] = 'User';
        $form = $this->getUserForm();
        if ($form->isValid($_POST)) {
            $u = $this->data['user'];
            $u->first_name = $_POST['first_name'];
            $u->last_name = $_POST['last_name'];
            $u->first_name_alphabet = $_POST['first_name_alphabet'];
            $u->last_name_alphabet = $_POST['last_name_alphabet'];
            $u->email = $_POST['email'];
            $u->password = $_POST['password'];
            $u->qq = $_POST['qq'];
            $u->confirm_code = $_POST['confirm_code'];
            $u->type = $_POST['type'];
            $u->status = $_POST['status'];
            $u->update(array('where'=>"id={$u->id}",'field'=>'email,type,first_name,last_name,password,qq,confirm_code,phone,status'));
            $this->data['message'] = 'updated';
            $form = $this->getUserForm();
        }
        $this->data['form'] = $form->render();
		$this->renderAction('/admin/user/edit');
	}

    public function activate() {
        $u = $this->data['user'];
    }

    private function setUser() {
        Doo::loadModel('User');
        if (isset($this->params['id'])) {
            $u = new User;
            if (is_numeric($this->params['id'])) {
                $u->id = $this->params['id'];
            } else {
                $u->confirm_code = $this->params['id'];
            }
            $user = $this->db()->find($u, array('limit'=>1));
            $this->data['user'] = $user;
        }
    }

    private function getUserForm() {
        Doo::loadHelper('DooForm');
        $u = $this->data['user'];
        if ($u->id) {
            $action = Doo::conf()->APP_URL . 'index.php/admin/users/'.$u->id;
        } else {
            $action = Doo::conf()->APP_URL . 'index.php/admin/users/save';
        }
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements' => array(
                 'first_name' => array('text', array(
                     'required' => true,
                     'label' => 'First Name:',
                     'value' => $u->first_name,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'last_name' => array('text', array(
                     'required' => true,
                     'label' => 'Last Name:',
                     'value' => $u->last_name,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'first_name_alphabet' => array('text', array(
                     'required' => true,
                     'label' => 'First Name(Pinyin):',
                     'value' => $u->first_name_alphabet,
                     'attributes' => array('class' => 'control textbox validate[required,alphabet()]'),
                 'element-wrapper' => 'div'
                 )),
                 'last_name_alphabet' => array('text', array(
                     'required' => true,
                     'label' => 'Last Name(Pinyin):',
                     'value' => $u->last_name_alphabet,
                     'attributes' => array('class' => 'control textbox validate[required,alphabet()]'),
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
                 'phone' => array('text', array(
                     'required' => true,
                     'label' => 'Phone:',
                     'value' => $u->phone,
                     'attributes' => array('class' => 'control textbox validate[number()]'),
                 'element-wrapper' => 'div'
                 )),
                 'qq' => array('text', array(
                     'required' => true,
                     'label' => 'QQ:',
                     'value' => $u->qq,
                     'attributes' => array('class' => 'control textbox validate[number()]'),
                 'element-wrapper' => 'div'
                 )),
                 'confirm_code' => array('text', array(
                     'required' => true,
                     'label' => 'Confirm Code:',
                     'value' => $u->confirm_code,
                     'attributes' => array('class' => 'control textbox'),
                     'element-wrapper' => 'div'
                 )),
                 'status' => array('select', array(
                     'required' => true,
                     'multioptions' => array('registered'=>'注册', 'active'=>'激活', 'obsolete'=>'过期'),
                     'label' => 'Status:',
                     'value' => $u->status,
                     'attributes' => array('class' => 'control type validate[required]'),
                     'element-wrapper' => 'div'
                 )),
                 'type' => array('select', array(
                     'required' => true,
                     'multioptions' => array('' => '' , 'customer'=>'客户', 'counselor'=>'咨询员', 'executor'=>'执行员', 'admin'=>'管理员'),
                     'label' => 'Type:',
                     'value' => $u->type,
                     'attributes' => array('class' => 'control type validate[required]'),
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
