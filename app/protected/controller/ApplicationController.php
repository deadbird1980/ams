<?php
require_once 'BaseController.php';

class ApplicationController extends BaseController {

    protected $user;
    protected $sortField = 'application.id';
    protected $orderType = 'desc';
    protected $helper = 'ApplicationHelper';

	public function index() {
        Doo::loadHelper('DooPager');
        Doo::loadModel('Application');
        $app = new Application();
        $options = $app->scopeSeenByUser($this->auth->user);
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
        if ($this->isPost() && isset($_POST['operation'])) {
            if ($_POST['operation'] == 'delete') {
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
                $url = Doo::conf()->APP_URL.$this->data['range'].'/users/'.$this->params['user_id'].'/applications/page';
            } else {
                $url = Doo::conf()->APP_URL.$this->data['range'].'/applications/page';
            }

            if($this->sortField=='Application.id' && $this->orderType=='desc'){
                $pager = new DooPager($url, $count, $page_size, $pages);
            }else{
                $pager = new DooPager(Doo::conf()->APP_URL.$this->data['range']."/applications/sort/{$this->sortField}/{$this->orderType}/page", $count, $page_size, $pages);
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
            if ($this->auth->user->isAdmin()) {
                $this->data['applications'] = $app->relateMany(array('User','Assignee'),array('User'=>$options));
            } else {
                $this->data['applications'] = $app->relateUser($options);
            }
            $this->data['sortField'] = $this->sortField;
        }

        if ($this->auth->user->isAdmin()) {
            $this->renderAction('/admin/application/index');
        } else {
            $this->renderAction('/my/application/index');
        }
	}

    public function create() {
        if ($this->isPost()) {
            Doo::loadModel('Application');
            $app = new Application($_POST);
            $app->user_id = $this->params['user_id'];
            $app->type = $_POST['type'];
            $app->assignee_id = $this->auth->user->id;
            $app->status = Application::CREATED;
            if ($id = $app->create($_POST)) {
                $hash = array('url'=>$id);
                $this->data['message'] = $this->t('application_created', $hash);
            }
            $this->renderAction('/my/application/created');
        } else {
            $form = $this->helper->getNewApplicationForm();
            $this->data['form'] = $form->render();
            $this->renderAction('/my/application/type');
        }
    }

    public function editType() {
        $app = Doo::loadModel('Application', true);
        $app = $this->data['application'] = $app->getById_first($this->params['id']);
        $form = $this->helper->getApplicationTypeForm($app);
        $id = $app->id;
        if ($this->isPost()) {
            if ($app->canChangeTo($_POST['type'])) {
                $app->update_attributes($_POST, array('where'=>"id=${id}"));
                if ($app->isSchool()) {
                    $app->getDetail()->update_attributes($_POST, array('where'=>"id=${id}"));
                }
            }
            $this->leaveMessage($this->t('updated'));
            return DooUrlBuilder::url2('ApplicationController', 'editType', array('id'=>$id), true);
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/application/type');
    }

    public function edit() {
        Doo::loadModel('Application');

        $app = new Application();
        $this->data['application'] = $app;
        if (isset($this->params['id'])) {
            $this->data['application'] = $app->getById_first($this->params['id']);
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
        Doo::loadModel('Application');

        $app = new Application();
        $this->data['application'] = $app;
        if (isset($this->params['id'])) {
            $app = $this->data['application'] = $app->getById_first($this->params['id']);
        } else {
        }
        $form = $this->helper->getApplicationStatusForm($app);

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

    public function uploadFiles() {
        Doo::loadModel('Application');
        $app = new Application();
        $this->data['application'] = $app->getById_first($this->params['id']);

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

}
?>
