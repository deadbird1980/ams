<?php
Doo::loadCore('view/DooView');
Doo::loadClass('Helper');
Doo::loadClass('EmailHelper');
Doo::loadModel('User');
Doo::loadModel('Application');
class TaskController extends DooCliController {
    private $data = array();

    public function __construct() {
        $this->emailHelper = new EmailHelper($this);
        $this->data['rootUrl'] = $this->data['baseurl'] = Doo::conf()->APP_URL;
    }

    public function checkCourse() {
        $this->notifyApplication2Send();
    }

    public function getData() {
        return $this->data;
    }

    protected function notifyApplication2Send() {
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
}
?>
