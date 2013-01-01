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

    public function apply() {
        $form = $this->getEuropeVisaForm();
        if ($this->isPost() && $form->isValid($_POST)) {
            Doo::loadModel('Application');
            $a = new Application();
            $a->user_id = $this->user->id;
            if ($this->user->activated_by) {
                $a->assignee_id = $this->user->activated_by;
            }
            $this->data['message'] = "User activated!";
            $a->type = 'visa';
            $a->status = 'in_progress';
            $id = $a->insert();
            return $this->APP_URL . "index.php/my/application/{$id}/files";
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/application/create');
    }

    public function uploadFile() {
        Doo::loadClass('UploadHandler');
        $handler = new UploadHandler();
        $handler->post(true);
    }

    public function uploadFiles() {
        $form = $this->getFilesForm();
        if ($this->isPost() && $form->isValid($_POST)) {
            $u = new User();
            $u->findByConfirm_code($_POST['confirm_code']);
            $u->activate($this->user);
            $this->data['message'] = "User activated!";
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/application/files');
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

    private function getFilesForm() {
        Doo::loadHelper('DooForm');
        $action = Doo::conf()->APP_URL . 'index.php/my/users/activate';
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements' => array(
                 'files' => array('display', array(
                     'content' => '文件:',
                 )),
                 'attachment' => array('file', array(
                     'required' => true,
                     'attributes' => array('class' => 'control multi max-15 accept-png|jpg validate[filesize]'),
                 'field-wrapper' => 'div'
                 )),
                 'submit' => array('submit', array(
                     'label' => "下一步",
                     'attributes' => array('class' => 'buttons'),
                     'order' => 100,
                 'field-wrapper' => 'div'
                 ))
             )
        ));
        return $form;
    }
    private function getEuropeVisaForm() {
        Doo::loadHelper('DooForm');
        $action = Doo::conf()->APP_URL . 'index.php/my/applications/create/'. $this->params['type'];
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements' => array(
                 'start_date' => array('text', array(
                     'label' => 'Start Date:',
                     'required' => true,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'passport' => array('display', array(
                     'content' => '护照信息:',
                 )),
                 'passport_no' => array('text', array(
                     'label' => '护照号码:',
                     'required' => true,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'passport_name' => array('text', array(
                     'label' => '护照姓名:',
                     'required' => true,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'birthday' => array('text', array(
                     'label' => '生日:',
                     'required' => true,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'passport_start_date' => array('text', array(
                     'label' => '护照生效期:',
                     'required' => true,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'passport_end_date' => array('text', array(
                     'label' => '护照截止日期:',
                     'required' => true,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'visa' => array('display', array(
                     'content' => '当前签证状态:',
                 )),
                 'address' => array('text', array(
                     'label' => '英国地址:',
                     'required' => true,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'visa_start_date' => array('text', array(
                     'label' => '签证开始日期:',
                     'required' => true,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'visa_end_date' => array('text', array(
                     'label' => '签证结束日期:',
                     'required' => true,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'work' => array('display', array(
                     'content' => '目前工作学习状况:',
                 )),
                 'company' => array('text', array(
                     'label' => '公司名称:',
                     'required' => true,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'university' => array('text', array(
                     'label' => '学校名称:',
                     'required' => true,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'files' => array('display', array(
                     'content' => '文件:',
                 )),
                 'submit' => array('submit', array(
                     'label' => "下一步",
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
