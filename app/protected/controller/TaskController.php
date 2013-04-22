<?php
Doo::loadCore('view/DooView');
Doo::loadClass('Helper');
Doo::loadClass('EmailHelper');
Doo::loadModel('User');
class TaskController extends DooCliController {
    private $data = array();

    public function __construct() {
        $this->emailHelper = new EmailHelper($this);
        $this->data['rootUrl'] = $this->data['baseurl'] = Doo::conf()->APP_URL;
    }

    public function checkCourse() {
        //$this->notifyApplication2Send();
        $this->notifyApplication2Reply();
    }

    public function getData() {
        return $this->data;
    }

    protected function notifyApplication2Send() {
        Doo::loadModel('Application');
        $app = new Application();
        $apps = $app->needToSend();
        foreach($apps as $app) {
            $this->data['application'] = $app;
            if ($app->executor_id) {
                $this->emailHelper->notifyUser($app->executor(), "{$app->User->first_name}的申请需要24小时内发送", 'need2send');
            } else {
                $this->emailHelper->notifyRole(User::EXECUTOR, "{$app->User->first_name}的申请需要24小时内发送", 'need2send');
            }
        }
    }

    protected function notifyApplication2Reply() {
        Doo::loadModel('CourseApplication');
        $app = new CourseApplication();
        $apps = $app->needToReply();
        foreach($apps as $app) {
            $this->data['application'] = $app;
            $this->data['school_application'] = $app->application();
            $this->data['student'] = $this->data['school_application']->user();
            $this->data['course_title'] = $app->title();
            $this->emailHelper->notifyUser($this->data['school_application']->executor(), "{$this->data['student']->first_name}的申请需要48小时内回复", 'need2reply');
        }
    }
}
?>
