<?php
require_once 'BaseController.php';

class MyController extends BaseController {

    protected $user;
    protected $sortField = 'application.id';
    protected $orderType = 'desc';
    protected $helper = 'ApplicationHelper';

    private function setMessage() {
        if ($todo = $this->auth->user->toDo()) {
            $this->data['message'] = $this->t($todo, array('url'=>'applications'));
        }
    }

	public function home() {
        $this->setMessage();
        $this->renderAction('/my/index');
	}

	public function listApplications() {
        Doo::loadHelper('DooPager');
        Doo::loadModel('Application');
        $app = new Application();
        $options = $app->scopeSeenByUser($this->auth->user);
        if (($count = $app->count($options)) > 0) {
            $row_perpage = $this->getPageSize();
            $pages = $this->getPages();
            //if default, no sorting defined by user, show this as pager link
            if(isset($this->params['orderType']))
                $this->orderType = $this->params['orderType'];
            if(isset($this->params['sortField']))
                $this->sortField = $this->params['sortField'];

            if($this->sortField=='application.id' && $this->orderType=='desc'){
                $pager = new DooPager(Doo::conf()->APP_URL.'my/applications/page', $app->count($options), $row_perpage, $pages);
            }else{
                $pager = new DooPager(Doo::conf()->APP_URL."my/applications/sort/$this->sortField/$this->orderType/page", $app->count($options), $row_perpage, $pages);
            }

            if(isset($this->params['pindex']))
                $pager->paginate(intval($this->params['pindex']));
            else
                $pager->paginate(1);

            $this->data['pager'] = $pager->output;

            $options['limit'] = $pager->limit;
            //Order by ASC or DESC
            if($this->orderType=='asc'){
                $options['asc'] = $this->sortField;
                $this->data['orderType'] = 'desc';
            }else{
                $options['desc'] = $this->sortField;
                $this->data['orderType'] = 'asc';
            }
            $this->data['applications'] = $app->relateMany(array('User','Assignee', 'CourseApplication'),array('User'=>$options));
        }
        $this->renderAction('/my/application/index');
	}

    public function profile() {
		$this->data['title'] = 'User';
        $form = $this->getProfileForm();
        if ($this->isPost() && $form->isValid($_POST)) {
            $u = $this->auth->user;
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
        Doo::loadModel('User');
        Doo::loadModel('Application');

        $app = new Application();
        // confirm all the files uploaded
        $app = $app->getById_first($this->params['id']);
        if ($app->isFilesReady()) {
            $app->submit();

            $this->notifyAdmin("Application {$app->id} submitted", "Application {$app->id} is submitted");
            $this->notifyRole(User::EXECUTOR, "Application {$app->id} submitted", "Application {$app->id} is submitted");
            $this->notifyUser($app->assignee(), "Application {$app->id} submitted", "Application {$app->id} is submitted");
            $this->data['message'] = $this->t('application_submitted');
            $this->renderAction('/my/application/submitted');
        } else {
            $this->leaveMessage($this->t('error_application_missing_files'));
            return Doo::conf()->APP_URL . "index.php/my/applications/{$app->id}/confirm";
        }
    }

    public function editApplication() {
        if (!($app = $this->setApplication())) {
            return array('no access', 404);
        }
        if ($this->data['application']->afterSubmitted()) {
            $form = $this->helper->getConfirmApplicationForm($app);
            $this->data['form'] = $form->render();
            $this->renderAction('/my/application/view');
        } else {
            $form = $this->helper->getApplicationForm($app);
            if ($this->isPost() && $form->isValid($_POST)) {
                $_POST = $this->helper->formatDate($_POST);
                $id = $this->params['id'];
                $app = new Application();
                $app = $app->getById_first($id);
                $app_detail = $app->createDetailApplication();
                $app_detail->update_attributes($_POST, array('where'=>"id=${id}"));
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
        $form = $this->getApplicationForm($app);
        if ($this->isPost() && $form->isValid($_POST)) {
            Doo::loadModel('Application');
            $a = new Application();
            $a->user_id = $this->auth->user->id;
            if ($this->auth->user->activated_by) {
                $a->assignee_id = $this->auth->user->activated_by;
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

    public function uploadFiles() {

        if (!($app = $this->setApplication())) {
            return array('no access', 404);
        }
        $form = $this->helper->getFilesForm($app);
        if ($this->isPost() && $form->isValid($_POST)) {
        }
        Doo::loadHelper('DooUrlBuilder');
        $this->data['prev_url'] = DooUrlBuilder::url2('MyController', 'editApplication', array('id'=>$this->params['id']), true);
        $this->data['next_url'] = DooUrlBuilder::url2('MyController', 'confirmApplication', array('id'=>$this->params['id']), true);
        $this->data['form'] = $form->render();
        // application file
        Doo::loadClass('FileHelper');
        $h = new FileHelper($this);
        $this->data['application_file'] = $h->getFilesForApplication($app);
        $files = $h->getFileNames($app);
        $this->data['instruction'] = $this->t('files_required') . join($files, ',');
        $this->data['files'] = $h->getFileJSON($app);
        if ($this->data['application']->beforeSubmitted()) {
            $this->renderAction('/my/application/file/edit');
        } else {
            $this->renderAction('/my/application/file/view');
        }
    }

    private function setApplication() {
        Doo::loadModel('Application');

        $app = new Application();
        $app = $this->data['application'] = $app->getById_first($this->params['id']);
        if ($app && !$app->canBeSeen($this->auth->user)) {
            $app = null;
        }
        return $app;
    }

    public function confirmApplication() {

        if (!($app = $this->setApplication())) {
            return array('no access', 404);
        }

        $form = $this->helper->getConfirmApplicationForm($app);
        if ($this->isPost()) {
            $app->doConfirm($this->auth->user);
            $this->notifyAdmin("Applicatioin {$app->id} is confirmed","Applicatioin {$app->id} is confirmed");
            return Doo::conf()->APP_URL . "index.php/my/applications";
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/application/confirm');
    }

    private function getProfileForm() {
        $u = $this->auth->user;
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
