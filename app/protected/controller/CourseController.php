<?php
require_once 'BaseController.php';

class CourseController extends BaseController {

    protected $user;
    protected $sortField = 'id';
    protected $orderType = 'desc';
    protected $helper = 'CourseHelper';

	public function index() {
        Doo::loadHelper('DooPager');
        Doo::loadModel('Application');
        $app = Doo::loadModel('Application', true);
        $app = $app->getById_first($this->params['id']);
        if (!$app->canBeSeen($this->auth->user)) {
            return array('not available', 404);
        }
        $options = array('where' => "application_id={$app->id}");
        $app = Doo::loadModel('CourseApplication', true);
        // operations
        if (($count = $app->count($options)) > 0) {
            if (isset($this->params['sortField'])) {
                $this->sortField = $this->params['sortField'];
            }
            if (isset($this->params['orderType'])) {
                $this->orderType = $this->params['orderType'];
            }
            $page_size = $this->getPageSize();
            $pages = $this->getPages();

            $url = Doo::conf()->APP_URL.$this->data['range'].'/applications/page';

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
            $this->data['applications'] = $app->find($options);
            $this->data['sortField'] = $this->sortField;
        }

        $this->renderAction('/my/application/course/index');
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

    public function edit() {
        $app = Doo::loadModel('CourseApplication', true);
        $app = $app->relateApplication_first(array('where'=>'course_application.id='.$this->params['id']));
        if (!$app->Application->canBeSeen($this->auth->user)) {
            return array('can not be seen', 404);
        }
        $this->data['application'] = $app;
        $form = $this->helper->getCourseForm($app);

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

    public function status() {
        $app = Doo::loadModel('CourseApplication', true);
        $app = $this->data['application'] = $app->getById_first($this->params['id']);

        $form = $this->helper->getCourseStatusForm($app);

        if ($this->isPost() && $form->isValid($_POST)) {
            $app->update_attributes($_POST, array('where'=>"id={$app->id}"));
            Doo::loadHelper('DooUrlBuilder');
            return DooUrlBuilder::url2('CourseController', 'index', array('id'=>$app->application_id), true);
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/application/status');
    }

    public function send() {
        $app = Doo::loadModel('CourseApplication', true);
        $app = $app->relateApplication_first(array('where'=>'course_application.id='.$this->params['id']));
        if (!$app->Application->canBeSeen($this->auth->user)) {
            return array('can not be seen', 404);
        }
        $app->send();
        return DooUrlBuilder::url2('CourseController', 'index', array('id'=>$app->application_id), true);
    }
}
?>
