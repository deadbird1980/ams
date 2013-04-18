<?php
class EmailHelper extends Helper {

    public function __construct($controller) {
        Doo::loadHelper('DooMailer');
        Doo::loadModel('User');
        Doo::loadCore('view/DooView');
        parent::__construct($controller);
    }

    public function notifyUser($user, $subject, $template) {
        $data = $this->controller->getData();
        $data['user'] = $user;
        $body = $this->renderEmail($template, $data);
        $this->sendMail($user, $subject, $body);
    }

    public function notifyAdmin($subject, $template) {
        $u = new User();
        $users = $u->getByType(User::ADMIN);
        foreach($admins as $admin) {
            $this->notifyUser($admin, $subject, $template);
        }
        return true;
    }

    public function notifyRole($role, $subject, $template) {
        $u = new User();
        $users = $u->getByType($role);
        foreach($users as $user) {
            $this->notifyUser($user, $subject, $template);
        }
    }

    protected function renderEmail($templatefile, $data) {
        $v = new DooView();
        ob_start();
        $v->render("/email/$templatefile", $data);
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }

    protected function sendMail($user, $subject, $body) {
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
