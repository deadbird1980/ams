<?php
require_once 'BaseController.php';
Doo::loadModel('CourseApplication');

class CourseController extends BaseController {

    protected $user;
    protected $sortField = 'id';
    protected $orderType = 'desc';
    protected $helper = 'CourseHelper';

	public function index() {
        Doo::loadHelper('DooPager');
        $app = Doo::loadModel('Application', true);
        $app = $app->getById_first($this->params['id']);
        if (!$app->canBeSeen($this->auth->user)) {
            return array('not available', 404);
        }
        $this->data['application'] = $app;
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
            $app = new CourseApplication();
            if ($id = $app->create($_POST)) {
                $hash = array('url'=>$id);
                $this->data['message'] = $this->t('application_created', $hash);
            }
            $this->renderAction('/my/application/created');
        } else {
            $app = new CourseApplication();
            $app->application_id = $this->params['id'];
            $form = $this->helper->getCourseEditForm($app);
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
        if (!($app = $this->setApplication())) {
            return array('no access', 404);
        }
        $app->send();
        $school_application = $app->application();
        $this->setEmailTags($app);
        $this->notifyUser($app->application()->assignee(), "课程申请{$app->id}已经发出", 'sent');
        return DooUrlBuilder::url2('CourseController', 'index', array('id'=>$app->application_id), true);
    }

    public function resend() {
        if (!($app = $this->setApplication())) {
            return array('no access', 404);
        }
        $app->resend();
        return DooUrlBuilder::url2('CourseController', 'index', array('id'=>$app->application_id), true);
    }

    public function reply() {
        if (!($app = $this->setApplication())) {
            return array('no access', 404);
        }
        $form = $this->helper->getCourseReplyForm($app);

        if ($this->isPost() && $form->isValid($_POST)) {
            $app->reply($_POST['result']);
            $attachment = Doo::loadModel('CourseApplicationAttachment', true);
            $attachment->course_application_id = $app->id;
            $attachment->application_id = $app->application_id;
            $attachment->type = 'reply';
            $options['upload_model'] = $attachment;
            $options['upload_dir'] = Doo::conf()->UPLOAD_PATH;
            Doo::loadClass('UploadHandler');
            $handler = new UploadHandler($options, false);
            $handler->post(false);
            //notify users
            $this->setEmailTags($app);
            $this->notifyUser($app->application()->assignee(), "课程申请{$app->id}已回复", 'replied');
            return DooUrlBuilder::url2('CourseController', 'index', array('id'=>$app->application_id), true);
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/application/course/reply');
    }

    public function choose() {
        $app = Doo::loadModel('CourseApplication', true);
        $app = $this->data['application'] = $app->getById_first($this->params['id']);

        $form = $this->helper->getCourseChooseForm($app);

        if ($this->isPost() && $form->isValid($_POST)) {
            $app->choose();
            $attachment = Doo::loadModel('CourseApplicationAttachment', true);
            $attachment->course_application_id = $app->id;
            $attachment->application_id = $app->application_id;
            $attachment->type = 'additional';
            $options['upload_model'] = $attachment;
            $options['upload_dir'] = Doo::conf()->UPLOAD_PATH;
            Doo::loadClass('UploadHandler');
            $handler = new UploadHandler($options, false);
            $handler->post(false);
            //notify users
            $this->setEmailTags($app);
            $this->notifyUser($app->application()->executor(), "课程申请{$app->id}被选中", "chosen");

            return DooUrlBuilder::url2('CourseController', 'index', array('id'=>$app->application_id), true);
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/application/course/reply');
    }

    public function reconfirm() {
        if (!($app = $this->setApplication())) {
            return array('no access', 404);
        }
        $form = $this->helper->getCourseConfirmForm($app);

        if ($this->isPost() && $form->isValid($_POST)) {
            $app->reconfirm();
            //notify users
            $this->setEmailTags($app);
            $this->notifyUser($app->application()->assignee(), "课程申请{$app->id}已确认", 'course_confirmed');

            return DooUrlBuilder::url2('CourseController', 'index', array('id'=>$app->application_id), true);
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/application/course/confirm');
    }

    public function finish() {
        $app = Doo::loadModel('CourseApplication', true);
        $app = $this->data['application'] = $app->getById_first($this->params['id']);

        $form = $this->helper->getCourseFinishForm($app);

        if ($this->isPost() && $form->isValid($_POST)) {
            $app->finish($_POST['result']);
            $attachment = Doo::loadModel('CourseApplicationAttachment', true);
            $attachment->course_application_id = $app->id;
            $attachment->application_id = $app->application_id;
            $attachment->type = 'finish';
            $options['upload_model'] = $attachment;
            $options['upload_dir'] = Doo::conf()->UPLOAD_PATH;
            Doo::loadClass('UploadHandler');
            $handler = new UploadHandler($options, false);
            $handler->post(false);
            $a = new Application();
            $a = $a->relateAssignee_first(array('where'=>"application.id={$app->application_id}"));
            //notify users
            $this->setEmailTags($app);
            $this->notifyUser($a->Assignee, "申请{$app->id}完成", 'completed');

            return DooUrlBuilder::url2('CourseController', 'index', array('id'=>$app->application_id), true);
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/application/course/reply');
    }

    public function files() {
        $app = Doo::loadModel('CourseApplication', true);
        $app = $this->data['application'] = $app->getById_first($this->params['id']);
        $this->renderAction('/my/application/course/file/view');
    }

    private function setApplication() {
        Doo::loadModel('CourseApplication');
        $app = new CourseApplication();
        $app = $this->data['application'] = $app->getById_first($this->params['id']);
        if ($app && !$app->canBeSeen($this->auth->user)) {
            $app = null;
        }
        return $app;
    }

    private function setEmailTags($app) {
        $this->data['school_application'] = $app->application();
        $this->data['student'] = $this->data['school_application']->user();
        $this->data['course_title'] = $app->title();
    }
}
?>
