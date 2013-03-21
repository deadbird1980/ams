<?php

class BaseController extends DooController {

    protected $data = array();
    protected $session;
    protected $auth;
    protected $translator;
    protected $user;
    protected $helper;

	public function beforeRun($resource, $action){

        $this->data['year'] = date('Y');
        $this->data['rootUrl'] = $this->data['baseurl'] = Doo::conf()->APP_URL;
        $this->data['indexUrl'] = Doo::conf()->APP_URL . 'index.php';
        $this->data['message'] = '';
        $this->session = Doo::session('ams');
        Doo::loadCore('auth/DooAuth');
        $this->auth = new DooAuth('ams');
		//if not login, group = anonymous
        $role = (isset($this->session->user['type'])) ? $this->session->user['type'] : 'anonymous';

        if($rs = $this->acl()->process($role, $resource, $action )){
            //echo $role .' is not allowed for '. $resource . ' '. $action;
            return $rs;
        }
        $this->data['role'] = $role;
        if ($this->session->user['id']) {
            Doo::loadModel('User');
            $u = new User();
            $this->user = $u->getById_first($this->session->user['id']);
            $this->data['range'] = $this->getRange();
        }
        $this->setTranslator();
        $this->setHelper();
        $this->pickMessage();
	}

    protected function pickMessage() {
        if (isset($this->session->message)) {
            $this->data['message'] = $this->session->message;
            unset($this->session->message);
        }
    }

    protected function leaveMessage($msg) {
        $this->session->message = $msg;
    }

    protected function setTranslator() {
        // “apc”, “php”, “xcache” and “eaccelerator”.
        // Doo::translator('Csv', Doo::getAppPath() . 'languages/'.Doo::conf()->lang.'/main.csv', array('cache' => 'php', 'delimiter' => '|'));
        Doo::translator('Csv', Doo::getAppPath() . 'languages/'.Doo::conf()->lang.'/main.csv', array('delimiter' => '|'));
    }

    protected function setHelper() {
        Doo::loadClass('Helper');
        if (isset($this->helper)) {
            Doo::loadClass($this->helper);
            $this->helper = new $this->helper($this);
        }
    }

    protected function renderAction($view, $layout = 'main') {

      //$this->view()->setDefaultRootViewPath(Doo::conf()->SITE_PATH . 'templates/' . $templateName . '/');
      //$this->view()->setRootCompiledPath(Doo::conf()->SITE_PATH . Doo::conf()->PROTECTED_FOLDER . "viewc/$userLanguage/$templateName/");

      if ($layout) {
         $this->view()->renderLayout($layout, $view, $this->data, NULL, Doo::conf()->TEMPLATE_COMPILE_ALWAYS);
      } else {
         $this->view()->render($view, $this->data, NULL, Doo::conf()->TEMPLATE_COMPILE_ALWAYS);
      }
    }

    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    public function getAuthenticityToken() {
        if (!isset($this->session->token)) {
            $token = md5(time() . rand(1,100) . Doo::conf()->SITE_ID);
            $this->session->token = $token;
        }
        return $this->session->token;
    }

    public function isValidToken() {
        if (!isset($_POST['token'])) {
            return false;
        }
        if ($this->session->token == $_POST['token']) {
            unset($this->session->token);
            return true;
        }
        return false;
    }

    protected function getRange() {
        if ($this->user && $this->user->isAdmin()) {
            return 'admin';
        } else {
            return 'my';
        }
    }

    public function isAdmin() {
        if ($this->user && $this->user->isAdmin()) {
            return true;
        }
        return false;
    }

    public function t($msg, $vars=null) {
        $str = Doo::getTranslator()->translate($msg);
        if ($vars && preg_match_all('/{{([^ \t\r\n\(\)\.}]+)}}/', $str, $tags)) {
            for($i=0; $i<count($tags[0]); $i++) {
                $str = str_replace("{$tags[0][$i]}", $vars[$tags[1][$i]], $str);
            }
        }
        return $str;
    }

    public function getPageSize() {
        if (isset(Doo::conf()->pagesize)) {
            return Doo::conf()->pagesize;
        }
        return 50;
    }

    public function getPages() {
        if (isset(Doo::conf()->pages)) {
            return Doo::conf()->pages;
        }
        return 10;
    }

    public function notifyAdmin($subject, $body) {
        Doo::loadModel('User');
        $u = new User();
        $admins = $u->getByType('admin');
        Doo::loadHelper('DooMailer');
        foreach($admins as $admin) {
            $mail = new DooMailer();
            $mail->addTo($admin->email, $admin->first_name);
            $mail->setSubject($subject);
            $mail->setBodyText($body);
            $mail->setBodyHtml($body);
            $mail->setFrom(Doo::conf()->support_email, 'no reply');
            if ($mail->send()) {

            }
        }
        return true;
    }

    public function notifyUser($user, $subject, $body) {
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
