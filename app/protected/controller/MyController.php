<?php
require_once 'BaseController.php';

class MyController extends BaseController {

    protected $user;
	public function beforeRun($resource, $action){
        if ($rtn = parent::beforeRun($resource, $action)) {
            return $rtn;
        }


        Doo::loadModel('User');

        $u = new User();
        $u->id = $this->session->user['id'];
        $this->user = $this->db()->find($u, array('limit'=>1));
        $this->sortField = '';
        $this->orderType = '';
	}

	public function home() {
        $this->renderAction('/my/index');
	}

	public function listApplications() {
        Doo::loadHelper('DooPager');
        Doo::loadModel('Application');
        $u = $this->user;
        $app = new Application();
        //if default, no sorting defined by user, show this as pager link
        if($this->sortField=='email' && $this->orderType=='desc'){
            $pager = new DooPager(Doo::conf()->APP_URL.'admin/user/page', $app->count($app->scopeSeenByUser($u)), 6, 10);
        }else{
            $pager = new DooPager(Doo::conf()->APP_URL."admin/user/sort/$this->sortField/$this->orderType/page", $app->count($app->seenByUser($u)), 6, 10);
        }

        if(isset($this->params['pindex']))
            $pager->paginate(intval($this->params['pindex']));
        else
            $pager->paginate(1);

        $this->data['pager'] = $pager->output;

        $columns = 'id,type,start_date,status,assignee_id,user_id';
        //Order by ASC or DESC
        if($this->orderType=='desc'){
            $this->data['applications'] = $app->limit($pager->limit, null, $this->sortField,
                                        array_merge(array('select'=>$columns), $app->scopeSeenByMe($u))
                                  );
            $this->data['order'] = 'asc';
        }else{
            $this->data['applications'] = $app->limit($pager->limit, $this->sortField, null,
                                        //we don't want to select the Content (waste of resources)
                                        array_merge(array('select'=>$columns), $app->scopeSeenByUser($u))
                                  );
            $this->data['order'] = 'desc';
        }
        $this->renderAction('/my/application/index');
	}

	public function listUsers() {
        Doo::loadHelper('DooPager');
        $user = $this->user;
        $u = new User;
        if ($u->count($user->scopeSeenByMe()) > 0) {
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
        }
        $form = $this->getActivateUserForm();
        $this->data['form'] = $form->render();
        //$this->renderAction('/my/'.$this->session->user['type'].'/users');
        $this->renderAction('/my/user/index');
	}

    public function profile() {
		$this->data['title'] = 'User';
        Doo::loadModel('User');
        $u = new User();
        $this->data['user'] = $u->getById_first($this->session->user['id']);
        $form = $this->getProfileForm();
        $this->data['form'] = $form->render();

		$this->renderAction('/my/profile');
    }

    public function activateUser() {
        $form = $this->getActivateUserForm();
        if ($this->isPost() && $form->isValid($_POST)) {
            $u = new User();
            $u->confirm_code = $_POST['confirm_code'];
            $u = $this->db()->find($u, array('limit'=>1));
            if (!$u->isRegistered()) {
                $this->data['message'] = "User already activiated!";
            } else {
                $u->activate($this->user->id);
                $this->data['message'] = "User activated!";
            }
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/user/activate');
    }

    public function submitApplication() {
        Doo::loadModel('Application');

        $app = new Application();
        $app->update_attributes(array("status"=>"submitted"), array('where'=>"id={$this->params['id']}"));
        $this->renderAction('/my/application/submitted');
    }

    public function editApplication() {
        Doo::loadModel('Application');

        $app = new Application();
        $this->data['application'] = $app->getById_first($this->params['id']);

        if ($this->data['application']->isSubmitted()) {
            $form = $this->getConfirmApplicationForm();
            $this->data['form'] = $form->render();
            $this->renderAction('/my/application/confirm');
        } else {
            $form = $this->getEuropeVisaForm($app);
            if ($this->isPost() && $form->isValid($_POST)) {
                $id = $this->params['id'];
                $app = new Application();
                $app = $app->getById_first($id);
                $visaapp = new VisaApplication();
                $visaapp = $visaapp->getById_first($id);
                $app = new VisaApplication($_POST);
                $app->id = $id;
                $app->update_attributes($_POST, array('where'=>"id=${id}"));
                Doo::loadHelper('DooUrlBuilder');
                return DooUrlBuilder::url2('MyController', 'uploadFiles', array('id'=>$id), true);
            }
            $this->data['form'] = $form->render();
            $this->renderAction('/my/application/edit');
        }
    }

    public function apply() {
        Doo::loadModel('Application');
        $app = new Application();
        $app->type = $this->params['type'];
        $this->data['application'] = $app;
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
            // application detail
            if ($this->params['type'] == 'visa') {
                Doo::loadModel('VisaApplication');
                $a = new VisaApplication($_POST);
                $a->id = $id;
                $a->insert();
            }
            return Doo::conf()->APP_URL . "index.php/my/applications/{$id}/files";
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/application/edit');
    }

    public function uploadFile() {
        Doo::loadClass('UploadHandler');
        $handler = new UploadHandler();
        $handler->post(true);
    }

    public function uploadFiles() {
        Doo::loadModel('Application');
        $app = new Application();
        $this->data['application'] = $app->getById_first($this->params['id']);

        $form = $this->getFilesForm();
        if ($this->isPost() && $form->isValid($_POST)) {
            $u = new User();
            $u->findByConfirm_code($_POST['confirm_code']);
            $u->activate($this->user);
            $this->data['message'] = "User activated!";
        }
        Doo::loadHelper('DooUrlBuilder');
        $this->data['prev_url'] = DooUrlBuilder::url2('MyController', 'editApplication', array('id'=>$this->params['id']), true);
        $this->data['next_url'] = DooUrlBuilder::url2('MyController', 'confirmApplication', array('id'=>$this->params['id']), true);
        $this->data['form'] = $form->render();
        if ($this->data['application']->isSubmitted()) {
            $this->renderAction('/my/application/file/view');
        } else {
            $this->renderAction('/my/application/file/edit');
        }
    }

    public function confirmApplication() {
        Doo::loadModel('Application');

        $app = new Application();
        $this->data['application'] = $app->getById_first($this->params['id']);

        $form = $this->getConfirmApplicationForm();
        if ($this->isPost() && $form->isValid($_POST)) {
            $id = $this->params['id'];
            $app = new Application();
            $app = $app->getById_first($id);
            $visaapp = new VisaApplication();
            $visaapp = $visaapp->getById_first($id);
            $app = new VisaApplication($_POST);
            $app->id = $id;
            $app->update_attributes($_POST, array('where'=>"id=${id}"));
            return Doo::conf()->APP_URL . "index.php/my/applications/{$id}/files";
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/application/edit');
    }

    private function getActivateUserForm() {
        Doo::loadHelper('DooForm');
        Doo::loadHelper('DooUrlBuilder');
        $action = DooUrlBuilder::url2('MyController', 'activateUser', null, true);
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
        Doo::loadHelper('DooUrlBuilder');
        $app = $this->data['application'];
        if ($app && $app->id) {
            $action = DooUrlBuilder::url2('MyController', 'editApplication', array('id'=>$app->id), true);
        } else {
            $action = DooUrlBuilder::url2('MyController', 'apply', array('type'=>$app->type), true);
        }
        Doo::loadModel('VisaApplication');
        $visaapp = new VisaApplication();
        $visaapp = $visaapp->getById_first($app->id);
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements' => array(
                 'type' => array('hidden', array(
                     'value' => $app->type,
                 )),
                 'start_date' => array('text', array(
                     'label' => $this->t('start_date'),
                     'required' => true,
                     'value' => $visaapp->start_date,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'passport' => array('display', array(
                     'content' => $this->t('passport_information'),
                 )),
                 'passport_no' => array('text', array(
                     'label' => $this->t('passport_no'),
                     'required' => true,
                     'value' => $visaapp->passport_no,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'passport_name' => array('text', array(
                     'label' => $this->t('passport_name'),
                     'required' => true,
                     'value' => $visaapp->passport_name,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'birthday' => array('text', array(
                     'label' => $this->t('birthday'),
                     'required' => true,
                     'value' => $visaapp->birthday,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'passport_start_date' => array('text', array(
                     'label' => $this->t('start_date'),
                     'required' => true,
                     'value' => $visaapp->passport_start_date,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'passport_end_date' => array('text', array(
                     'label' => $this->t('end_date'),
                     'required' => true,
                     'value' => $visaapp->passport_end_date,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'visa' => array('display', array(
                     'content' => $this->t('visa_status'),
                 )),
                 'address' => array('text', array(
                     'label' => $this->t('uk_address'),
                     'required' => true,
                     'value' => $visaapp->address,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'visa_start_date' => array('text', array(
                     'label' => $this->t('start_date'),
                     'required' => true,
                     'value' => $visaapp->visa_start_date,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'visa_end_date' => array('text', array(
                     'label' => $this->t('end_date'),
                     'required' => true,
                     'value' => $visaapp->visa_end_date,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'work' => array('display', array(
                     'content' => $this->t('work_study_information'),
                 )),
                 'organization' => array('text', array(
                     'label' => $this->t('organization'),
                     'required' => true,
                     'value' => $visaapp->organization,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'submit' => array('submit', array(
                     'label' => $this->t('next'),
                     'attributes' => array('class' => 'buttons'),
                     'order' => 100,
                 'field-wrapper' => 'div'
                 ))
             )
        ));
        return $form;
    }

    private function getConfirmApplicationForm() {
        Doo::loadHelper('DooForm');
        Doo::loadHelper('DooUrlBuilder');
        $app = $this->data['application'];
        $action = DooUrlBuilder::url2('MyController', 'submitApplication', array('id'=>$app->id), true);
        Doo::loadModel('VisaApplication');
        $visaapp = new VisaApplication();
        $visaapp = $visaapp->getById_first($app->id);
        $elements =  array(
                 'type' => array('hidden', array(
                     'value' => $app->type,
                 )),
                 'start_date' => array('display', array(
                     'label' => $this->t('start_date'),
                     'content' => $visaapp->start_date,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'passport' => array('display', array(
                     'content' => '护照信息:',
                 )),
                 'passport_no' => array('display', array(
                     'label' => $this->t('passport_no'),
                     'content' => $visaapp->passport_no,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'passport_name' => array('display', array(
                     'label' => $this->t('passport_name'),
                     'content' => $visaapp->passport_name,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'birthday' => array('display', array(
                     'label' => $this->t('birthday'),
                     'content' => $visaapp->birthday,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'passport_start_date' => array('display', array(
                     'label' => $this->t('start_date'),
                     'content' => $visaapp->passport_start_date,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'passport_end_date' => array('display', array(
                     'label' => $this->t('end_date'),
                     'content' => $visaapp->passport_end_date,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'visa' => array('display', array(
                     'content' => '当前签证状态:',
                 )),
                 'address' => array('display', array(
                     'label' => $this->t('uk_address'),
                     'content' => $visaapp->address,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'visa_start_date' => array('display', array(
                     'label' => $this->t('start_date'),
                     'content' => $visaapp->visa_start_date,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'visa_end_date' => array('display', array(
                     'label' => $this->t('end_date'),
                     'content' => $visaapp->visa_end_date,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'work' => array('display', array(
                     'content' => '目前工作学习状况:',
                 )),
                 'organization' => array('display', array(
                     'label' => $this->t('organization'),
                     'content' => $visaapp->organization,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )));
        if (!$app->isSubmitted()) {
            $elements['submit'] = array('submit', array(
                     'label' => $this->t('submit'),
                     'attributes' => array('class' => 'buttons'),
                     'order' => 100,
                 'field-wrapper' => 'div'
                 ));
        }
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements' => $elements
        ));
        return $form;
    }

    private function getProfileForm() {
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
