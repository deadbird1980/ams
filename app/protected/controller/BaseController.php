<?php

class BaseController extends DooController {

    protected $data = array();
    protected $session;
    protected $auth;
    protected $translator;
    protected $user;

	public function beforeRun($resource, $action){

        $this->data['rootUrl'] = $this->data['baseurl'] = Doo::conf()->APP_URL;
        $this->data['message'] = '';
        $this->session = Doo::session('ams');
        Doo::loadCore('auth/DooAuth');
        $this->auth = new DooAuth('ams');
		//if not login, group = anonymous
        $role = (isset($this->session->user['type'])) ? $this->session->user['type'] : 'anonymous';

        if($rs = $this->acl()->process($role, $resource, $action )){
            //echo $role .' is not allowed for '. $resource . ' '. $action;
            //print_r($rs);
            return $rs;
        }
        $this->data['role'] = $role;
        // “apc”, “php”, “xcache” and “eaccelerator”.
        // Doo::translator('Csv', Doo::getAppPath() . 'languages/'.Doo::conf()->lang.'/main.csv', array('cache' => 'php', 'delimiter' => '|'));
        Doo::translator('Csv', Doo::getAppPath() . 'languages/'.Doo::conf()->lang.'/main.csv', array('delimiter' => '|'));
        if ($this->session->user['id']) {
            Doo::loadModel('User');
            $u = new User();
            $this->user = $u->getById_first($this->session->user['id']);
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

    protected function t($msg) {
        return Doo::getTranslator()->translate($msg);
    }
}
?>
