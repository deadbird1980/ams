<?php
require_once 'BaseController.php';

class UserController extends BaseController {
	public function beforeRun($resource, $action){
        if ($rtn = parent::beforeRun($resource, $action)) {
            return $rtn;
        }
        $this->setUser();
        $this->sortField = 'id';
        $this->orderType = 'desc';
        if (isset($this->params['sortField'])) {
          $this->data['sortField'] = $this->sortField = $this->params['sortField'];
        }
        if (isset($this->params['orderType'])) {
          $this->orderType = $this->params['orderType'];
        }
    }

	public function index() {
        Doo::loadHelper('DooPager');
        $user = $this->user;
        $u = new User;
        $scope = $user->scopeSeenByMe();
        if (($user_count = $u->count($scope)) > 0) {
        //if default, no sorting defined by user, show this as pager link
            $row_perpage = Doo::conf()->ROWS_PERPAGE;
            $pages = Doo::conf()->PAGES;
            if($this->sortField=='email' && $this->orderType=='desc'){
                $pager = new DooPager(Doo::conf()->APP_URL.$this->getRange().'/users/page', $user_count, $row_perpage, $pages);
            }else{
                $pager = new DooPager(Doo::conf()->APP_URL.$this->getRange()."/users/sort/{$this->sortField}/{$this->orderType}/page", $user_count, $row_perpage, $pages);
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
                                            array_merge(array('select'=>$columns), $scope)
                                      );
                $this->data['orderType'] = 'asc';
            }else{
                $this->data['users'] = $u->limit($pager->limit, $this->sortField, null,
                                            //we don't want to select the Content (waste of resources)
                                            array_merge(array('select'=>$columns), $scope)
                                      );
                $this->data['orderType'] = 'desc';
            }
        }
        $form = $this->getActivateUserForm();
        $this->data['form'] = $form->render();
        if ($this->user->isAdmin()) {
            $this->renderAction('/admin/user/index');
        } else {
            $this->renderAction('/my/user/index');
        }
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
        if ($this->isPost() && $form->isValid($_POST)) {
            $u = $this->data['user'];
            $u->first_name = $_POST['first_name'];
            $u->last_name = $_POST['last_name'];
            $u->first_name_alphabet = $_POST['first_name_alphabet'];
            $u->last_name_alphabet = $_POST['last_name_alphabet'];
            $u->email = $_POST['email'];
            $u->password = $_POST['password'];
            $u->qq = $_POST['qq'];
            $u->confirm_code = $_POST['confirm_code'];
            if ($this->user->isAdmin() && isset($_POST['type'])) {
                $u->type = $_POST['type'];
            }
            $u->status = $_POST['status'];
            $u->update(array('where'=>"id={$u->id}"));
            $this->data['message'] = 'updated';
            $form = $this->getUserForm();
        }
        $this->data['form'] = $form->render();
		$this->renderAction('/my/user/edit');
	}

    public function activate() {
        $form = $this->getActivateUserForm();
        if ($this->isPost() && $form->isValid($_POST)) {
            $u = new User();
            $u->confirm_code = $_POST['confirm_code'];
            $u = $this->db()->find($u, array('limit'=>1));
            if (!$u->isRegistered()) {
                $this->data['message'] = $this->t('already_activated');
            } else {
                $u->activate($this->user->id);
                $this->data['message'] = $this->t('user_activated');
            }
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/user/activate');
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
            $action = Doo::conf()->APP_URL . 'index.php/my/users/'.$u->id;
        } else {
            $action = Doo::conf()->APP_URL . 'index.php/my/users/save';
        }
        $elements = array(
                 'first_name' => array('text', array(
                     'required' => true,
                     'label' => $this->t('first_name'),
                     'value' => $u->first_name,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'last_name' => array('text', array(
                     'required' => true,
                     'label' => $this->t('last_name'),
                     'value' => $u->last_name,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'first_name_alphabet' => array('text', array(
                     'required' => true,
                     'label' => $this->t('first_name_pinyin'),
                     'value' => $u->first_name_alphabet,
                     'attributes' => array('class' => 'control textbox validate[required,alphabet()]'),
                 'element-wrapper' => 'div'
                 )),
                 'last_name_alphabet' => array('text', array(
                     'required' => true,
                     'label' => $this->t('last_name_pinyin'),
                     'value' => $u->last_name_alphabet,
                     'attributes' => array('class' => 'control textbox validate[required,alphabet()]'),
                 'element-wrapper' => 'div'
                 )),
                 'password' => array('password', array(
                     'required' => true,
                     'validators' => array('password'),
                     'label' => $this->t('password'),
                     'value' => $u->password,
                 'attributes' => array('class' => 'control password validate[required,length(6,10)]'),
                 'element-wrapper' => 'div'
                 )),
                 'email' => array('text', array(
                     'required' => true,
                     'validators' => array(array('email')),
                     'label' => $this->t('email'),
                     'value' => $u->email,
                     'attributes' => array('class' => 'control email validate[required,email]'),
                 'element-wrapper' => 'div'
                 )),
                 'phone' => array('text', array(
                     'required' => true,
                     'label' => $this->t('phone'),
                     'value' => $u->phone,
                     'attributes' => array('class' => 'control textbox validate[number()]'),
                 'element-wrapper' => 'div'
                 )),
                 'qq' => array('text', array(
                     'required' => true,
                     'label' => $this->t('qq'),
                     'value' => $u->qq,
                     'attributes' => array('class' => 'control textbox validate[number()]'),
                 'element-wrapper' => 'div'
                 )),
                 'confirm_code' => array('text', array(
                     'required' => true,
                     'label' => $this->t('confirm_code'),
                     'value' => $u->confirm_code,
                     'attributes' => array('class' => 'control textbox'),
                     'element-wrapper' => 'div'
                 )),
                 'status' => array('select', array(
                     'required' => true,
                     'multioptions' => array('registered'=>'注册', 'active'=>'激活', 'obsolete'=>'过期'),
                     'label' => $this->t('status'),
                     'value' => $u->status,
                     'attributes' => array('class' => 'control type validate[required]'),
                     'element-wrapper' => 'div'
                 )),
             );
        if ($this->user->isAdmin()) {
        $elements['type'] = array('select', array(
                     'required' => true,
                     'multioptions' => array('' => '' , 'customer'=>'客户', 'counselor'=>'咨询员', 'executor'=>'执行员', 'admin'=>'管理员'),
                     'label' => 'Type:',
                     'value' => $u->type,
                     'attributes' => array('class' => 'control type validate[required]'),
                     'element-wrapper' => 'div'
                 ));
        }
        $elements['submit'] = array('submit', array(
                     'label' => "Save",
                     'attributes' => array('class' => 'buttons'),
                     'order' => 100,
                 'field-wrapper' => 'div'
                 ));
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements' => $elements
        ));
        return $form;
    }

    private function getActivateUserForm() {
        Doo::loadHelper('DooForm');
        Doo::loadHelper('DooUrlBuilder');
        $action = DooUrlBuilder::url2('UserController', 'activate', null, true);
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
