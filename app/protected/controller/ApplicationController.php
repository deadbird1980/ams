<?php
require_once 'BaseController.php';
Doo::loadModel('Application');

class ApplicationController extends BaseController {

    protected $user;
    protected $sortField = 'application.id';
    protected $orderType = 'desc';
    protected $helper = 'ApplicationHelper';

	public function index() {
        Doo::loadHelper('DooPager');
        $app = new Application();
        $options = $app->scopeSeenByUser($this->auth->user);
        $form = $this->helper->getApplicationSearchForm();
        if(isset($this->params['user_id'])) {
            if ($u = $this->auth->user->getById_first($this->params['user_id'])) {
                if ($u->isAvailabeTo($this->auth->user)) {
                    $options['where'] = "{$options['where']} and user_id={$u->id}";
                } else {
                    return array('not available', 404);
                }
            } else {
                //not available to current user
                return array('not available', 404);
            }
            $this->data['user_id'] = $this->params['user_id'];
        } else {
            $u = $this->auth->user;
        }
        // operations
        if ($this->isPost()) {
            if (isset($_POST['command']) && $_POST['command'] == 'search' && $form->isValid($_POST)) {
                //search
                $options['where'] = "{$options['where']} and application.type='{$_POST['type']}'";
            } elseif (isset($_POST['operation']) && $_POST['operation'] == 'delete') {
                foreach($_POST['applications'] as $app_id) {
                    $app2delete = $app->getById_first($app_id);
                    $app2delete->delete();
                }
                $this->data['message'] = $this->t('item_deleted');
            } else if ($_POST['operation'] == 'paid') {
                foreach($_POST['applications'] as $app_id) {
                    $app2pay = $app->getById_first($app_id);
                    $app2pay->paid();
                }
                $this->data['message'] = $this->t('item_deleted');
            } else if ($_POST['operation'] == 'export') {
                foreach($_POST['applications'] as $app_id) {
                    $app2export = $app->getById_first($app_id);
                    $app2export->export();
                }
                $this->data['message'] = $this->t('item_deleted');
            }
        }

        if (($count = $app->count($options)) > 0) {
            if (isset($this->params['sortField'])) {
                $this->sortField = $this->params['sortField'];
            }
            if (isset($this->params['orderType'])) {
                $this->orderType = $this->params['orderType'];
            }
            $page_size = $this->getPageSize();
            $pages = $this->getPages();

            if (isset($this->params['user_id'])) {
                $url = Doo::conf()->APP_URL.$this->data['range'].'/users/'.$this->params['user_id'].'/applications/';
            } else {
                $url = Doo::conf()->APP_URL.$this->data['range'].'/applications/';
            }

            if($this->sortField=='Application.id' && $this->orderType=='desc'){
                $url .= 'page';
                $pager = new DooPager($url, $count, $page_size, $pages);
            }else{
                $url .= "sort/{$this->sortField}/{$this->orderType}/page";
                $pager = new DooPager($url, $count, $page_size, $pages);
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
                $this->data['order'] = 'asc';
                $this->data['orderType'] = 'desc';
            }else{
                $options['desc'] = $this->sortField;
                $this->data['order'] = 'desc';
                $this->data['orderType'] = 'asc';
            }

            $this->data['applications'] = $app->relateMany(array('User','Assignee', 'CourseApplication'),array('User'=>$options));
            $this->data['sortField'] = $this->sortField;
        }

        $this->data['form'] = $form->render();
        if ($this->auth->user->isAdmin()) {
            $this->renderAction('/admin/application/index');
        } else {
            $this->renderAction('/my/application/index');
        }
	}

    public function create() {
        if ($this->isPost()) {
            $app = new Application($_POST);
            $app->user_id = $this->params['user_id'];
            $app->type = $_POST['type'];
            $app->assignee_id = $this->auth->user->id;
            $app->status = Application::CREATED;
            if ($id = $app->create($_POST)) {
                $hash = array('url'=>$id);
                $this->data['message'] = $this->t('application_created', $hash);
            } else {
                $this->data['message'] = 'school must be filled in';
            }
            $this->renderAction('/my/application/created');
        } else {
            $form = $this->helper->getNewApplicationForm();
            $this->data['form'] = $form->render();
            $this->renderAction('/my/application/type');
        }
    }

    public function editType() {
        if (!($app = $this->setApplication())) {
            return array('no access', 404);
        }
        $form = $this->helper->getApplicationTypeForm($app);
        if ($this->isPost()) {
            if ($app->canChangeTo($_POST['type'])) {
                $app->updateType($_POST);
                $this->leaveMessage($this->t('updated'));
            }
            return DooUrlBuilder::url2('ApplicationController', 'editType', array('id'=>$app->id), true);
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/application/type');
    }

    public function edit() {
        $app = new Application();
        $this->data['application'] = $app;
        if (isset($this->params['id'])) {
            $app = $this->data['application'] = $app->getById_first($this->params['id']);
        }
        $form = $this->helper->getApplicationForm($app);

        if ($this->isPost() && $form->isValid($_POST)) {
            $id = $this->params['id'];
            $app = new Application();
            $app = $app->getById_first($id);
            $visaapp = $app->createDetailApplication();
            $app->update_attributes($_POST, array('where'=>"id=${id}"));
            Doo::loadHelper('DooUrlBuilder');
            return DooUrlBuilder::url2('MyController', 'uploadFiles', array('id'=>$id), true);
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/application/edit');
    }

    public function courses() {
        Doo::loadModel('Application');

        $app = new Application();
        $this->data['application'] = $app;
        if (isset($this->params['id'])) {
            $app = $this->data['application'] = $app->getById_first($this->params['id']);
        } else {
        }
        $form = $this->helper->getCourseForm($app);

        if ($this->isPost() && $form->isValid($_POST)) {
            $id = $this->params['id'];
            $app = new Application();
            $app = $app->getById_first($id);
            $visaapp = $app->createDetailApplication();
            $app->update_attributes($_POST, array('where'=>"id=${id}"));
            Doo::loadHelper('DooUrlBuilder');
            return DooUrlBuilder::url2('MyController', 'listApplications', array('id'=>$id), true);
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/application/status');
    }

    public function status() {
        if (!($app = $this->setApplication())) {
            return array('no access', 404);
        }

        $form = $this->helper->getApplicationStatusForm($app);

        if ($this->isPost() && $form->isValid($_POST)) {
            $app->update_attributes($_POST, array('where'=>"id={$app->id}"));
            return Doo::conf()->APP_URL.$this->data['range'].'/applications/';
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/application/status');
    }

    public function uploadFiles() {
        if (!($app = $this->setApplication())) {
            return array('no access', 404);
        }
        Doo::loadModel('Application');
        $this->data['application'] = $app;

        $form = $this->getFilesForm();
        if ($this->isPost() && $form->isValid($_POST)) {
            $u = new User();
            $u->findByConfirm_code($_POST['confirm_code']);
            $u->activate($this->auth->user);
            $this->data['message'] = "User activated!";
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/application/files');
    }

    public function confirm() {
        if (!($app = $this->setApplication()) || !$app->canBeModified($this->auth->user)) {
            return array('no access', 404);
        }

        if ($app->isResubmitted()) {
            return Doo::conf()->APP_URL . "my/courses/{$app->chosen()->id}/reconfirm";
        }

        $this->data['application'] = $app;

        $form = $this->helper->getConfirmApplicationForm($app);
        if ($this->isPost() && $form->isValid($_POST)) {
            $this->data['student'] = $app->user();
            if ($_POST['action'] == '1') {
                $app->confirm($this->auth->user);
                $this->notifyUser($app->assignee(), "Applicatioin {$app->id} is confirmed",'confirmed');
            } elseif ($_POST['action'] == '2') {
                $app->reject($this->auth->user, $_POST['comment']);
                $this->notifyUser($app->assignee(), "申请{$app->id}被退回","rejected");
            }
            return Doo::conf()->APP_URL . "index.php/my/applications";
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/application/confirm');
    }

    public function submit() {
        Doo::loadModel('User');
        if (!($app = $this->setApplication())) {
            return array('no access', 404);
        }

        if ($app->isFilesReady()) {
            $app->submit();
            $this->data['student'] = $app->user();
            $this->notifyRole(User::EXECUTOR, "Application {$app->id} submitted", "submitted");
            $this->notifyUser($app->assignee(), "Application {$app->id} submitted", "submitted");
            $this->data['message'] = $this->t('application_submitted');
            $this->renderAction('/my/application/submitted');
        } else {
            $this->leaveMessage($this->t('error_application_missing_files'));
            return Doo::conf()->APP_URL . "index.php/my/applications/{$app->id}/confirm";
        }
    }

    public function email() {
        $app = new Application();
        $this->data['application'] = $app;
        $this->data['user'] = $this->auth->user;
        if (isset($this->params['id'])) {
            $app = $this->data['application'] = $app->getById_first($this->params['id']);
        }
        print $this->renderEmail($this->params['template'], $this->data);
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

}
?>
