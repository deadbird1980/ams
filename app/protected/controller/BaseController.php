<?php
Doo::loadCore('auth/DooAuth');
Doo::loadModel('User');

class BaseController extends DooController {

    protected $data = array();
    protected $auth;
    protected $translator;
    protected $helper;

	public function beforeRun($resource, $action){

        $this->data['year'] = date('Y');
        $this->data['rootUrl'] = $this->data['baseurl'] = Doo::conf()->APP_URL;
        $this->data['indexUrl'] = Doo::conf()->APP_URL . 'index.php';
        $this->data['message'] = '';
        $this->auth = new DooAuth('ams');
        $this->auth->setSecurityLevel(3);
        $this->auth->setSalt(Doo::conf()->SITE_ID);
        $this->auth->start();
		//if not login, group = anonymous
        $role = isset($this->auth->group) ? $this->auth->group : 'anonymous';

        if($rs = $this->acl()->process($role, $resource, $action )){
            //echo $role .' is not allowed for '. $resource . ' '. $action; exit;
            return $rs;
        }
        $this->data['role'] = $role;
        if (isset($this->auth->group)) {
            if ($this->auth->group == 'admin') {
                $this->data['range'] = 'admin';
            } else {
                $this->data['range'] = 'my';
            }
            $this->auth->setSecurityLevel(1);
        } else {
            $this->auth->setData('nouser', $role);
        }
        $this->setTranslator();
        $this->setHelper();
        $this->pickMessage();
	}

    protected function pickMessage() {
        if (isset($this->auth->message) && strlen($this->auth->message) > 0) {
            $this->data['message'] = $this->auth->message;
            unset($this->auth->message);
        }
    }

    protected function leaveMessage($msg) {
        $this->auth->message = $msg;
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
        return $this->auth->securityToken();
    }

    public function isValidToken() {
        if (!isset($_POST['token'])) {
            return false;
        }
        if ($this->auth->validateForm($_POST['token'])) {
            return true;
        }
        return false;
    }

    public function isAdmin() {
        if ($this->auth->user && $this->auth->user->isAdmin()) {
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

    public function notifyAdmin($subject, $template) {
        Doo::loadModel('User');
        $u = new User();
        $admins = $u->getByType('admin');
        $body = $this->renderEmail($template, $this->data);
        foreach($admins as $admin) {
            $this->sendMail($admin, $subject, $body);
        }
        return true;
    }

    public function notifyUser($user, $subject, $template) {
        $this->data['user'] = $user;
        $body = $this->renderEmail($template, $this->data);
        $this->sendMail($user, $subject, $body);
    }

    public function notifyRole($role, $subject, $template) {
        Doo::loadHelper('DooMailer');
        $u = Doo::loadModel('User', true);
        $users = $u->getByGroup($role);
        foreach($users as $user) {
            $this->notifyUser($user, $subject, $template);
        }
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

    public function isDev() {
        return Doo::conf()->APP_MODE == 'dev';
    }

    public function getRange() {
        if ($this->auth->user && $this->auth->user->isAdmin()) {
            return 'admin';
        } else {
            return 'my';
        }
    }

    public function renderEmail($templatefile, $data) {
        ob_start();
        $this->view()->render("/email/$templatefile", $data);
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }
}
?>
