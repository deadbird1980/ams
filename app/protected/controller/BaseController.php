<?php

class BaseController extends DooController {

    protected $data = array();
    protected $session;
    protected $auth;

	public function beforeRun($resource, $action){

        $this->data['rootUrl'] = $this->data['baseurl'] = Doo::conf()->APP_URL;
        $this->data['message'] = '';
        $this->session = Doo::session('ams');
        Doo::loadCore('auth/DooAuth');
        $this->auth = new DooAuth('ams');
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
}
?>
