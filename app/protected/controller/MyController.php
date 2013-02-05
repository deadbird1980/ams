<?php
require_once 'BaseController.php';

class MyController extends BaseController {

    protected $user;
    protected $sortField;
    protected $orderType;
    protected $helper = 'ApplicationHelper';

	public function home() {
        $this->renderAction('/my/index');
	}

	public function listApplications() {
        Doo::loadHelper('DooPager');
        Doo::loadModel('Application');
        $app = new Application();
        $options = $app->scopeSeenByUser($this->user);
        if ($count = $app->count($options) > 0) {
            //if default, no sorting defined by user, show this as pager link
            if($this->sortField=='email' && $this->orderType=='desc'){
                $pager = new DooPager(Doo::conf()->APP_URL.'my/applications/page', $app->count($options), 6, 10);
            }else{
                $pager = new DooPager(Doo::conf()->APP_URL."my/applications/sort/$this->sortField/$this->orderType/page", $app->count($options), 6, 10);
            }

            if(isset($this->params['pindex']))
                $pager->paginate(intval($this->params['pindex']));
            else
                $pager->paginate(1);

            $this->data['pager'] = $pager->output;

            $options['limit'] = $pager->limit;
            //Order by ASC or DESC
            if($this->orderType=='desc'){
                $options['asc'] = $this->sortField;
                $this->data['order'] = 'asc';
            }else{
                $options['desc'] = $this->sortField;
                $this->data['order'] = 'desc';
            }
            $this->data['applications'] = $app->relateUser($options);
        }
        $this->renderAction('/my/application/index');
	}

    public function profile() {
		$this->data['title'] = 'User';
        $form = $this->getProfileForm();
        if ($this->isPost() && $form->isValid($_POST)) {
            $u = $this->user;
            $u->first_name = $_POST['first_name'];
            $u->last_name = $_POST['last_name'];
            $u->first_name_alphabet = $_POST['first_name_alphabet'];
            $u->last_name_alphabet = $_POST['last_name_alphabet'];
            $u->email = $_POST['email'];
            $u->password = $_POST['password'];
            $u->phone = $_POST['phone'];
            $u->qq = $_POST['qq'];
            $u->update(array('where'=>"id={$u->id}"));
            $this->data['message'] = 'updated';
            $form = $this->getProfileForm();
        }
        $this->data['form'] = $form->render();

		$this->renderAction('/my/profile');
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
            $form = $this->helper->getEuropeVisaForm($app);
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

    private function getProfileForm() {
        $u = $this->user;
        Doo::loadHelper('DooForm');
        $action = Doo::conf()->APP_URL . 'index.php/my/profile';
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements' => array(
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
