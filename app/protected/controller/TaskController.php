<?php
Doo::loadCore('view/DooView');
Doo::loadModel('User');
Doo::loadModel('Application');
class TaskController extends DooCliController {
    private $data = array();

    public function checkCourse() {
        $app = new Application();
        $apps = $app->needToSend();
        foreach($apps as $app) {
            print "{$app->id}\n";
            if ($app->executor_id) {
                $this->notifyUser($app->executor(), '24小时内发送', 'needtosend');
            } else {
            }
        }
    }

    public function notifyUser($user, $subject, $template) {
        $this->data['user'] = $user;
        $body = $this->renderEmail($template, $this->data);
        $this->sendMail($user, $subject, $body);
    }

    public function renderEmail($templatefile, $data) {
        $v = new DooView();
        ob_start();
        $v->render("/email/$templatefile", $data);
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }

    public function sendMail($user, $subject, $body) {
        Doo::loadHelper('DooMailer');
        $mail = new DooMailer();
        $mail->addTo($user->email, $user->first_name);
        $mail->setSubject($subject);
        $mail->setBodyText($body);
        $mail->setBodyHtml($body);
        $mail->setFrom(Doo::conf()->support_email, 'no reply');
        if ($mail->send()) {
        }
        return true;
    }
}
?>
