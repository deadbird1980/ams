<?php
require_once 'BaseController.php';

class AccountController extends BaseController{

    public function index(){
		if(isset($this->session->user)){
			$this->data['user'] = $this->session->user;
            return $this->afterLogin();
		}else{
			$this->data['user'] = null;
		}

        $this->data['message'] = '';
        Doo::loadHelper('DooForm');
        $this->data['form'] = $this->getLoginForm()->render();

        $this->renderAction('login');
    }

    public function captcha() {
        $f = $this->params['file'];
        $this->setContentType('jpg');
        echo file_get_contents(Doo::conf()->TMP_PATH.'/'.$f);
    }

    public function registration(){
        $this->data['form'] = $this->getRegisterForm()->render();
        $this->renderAction('registration');
    }

    public function register(){
        if (md5(md5(md5(strtolower($_POST['captcha_code'])))) !=  @$_COOKIE['captcha']) {
          $this->data['message'] = 'Please input right string from the image';
          return $this->renderAction('registration');
        }
        Doo::loadModel('User');
        $user = new User();
        $user->email = $_POST['email'];
        $user->password = $_POST['password'];
        if ($user->find(array('select'=>'id', 'limit'=>1)) != Null) {
          $this->data['message'] = 'User name exists, please try another one';
          return $this->renderAction('registration');
        }
        $user->insert();
        $this->data['message'] = 'User registered';
        $this->renderAction('registered');
    }


    public function login(){
        $form = $this->getLoginForm();
        if ($form->isValid($_POST)) {
            if(isset($_POST['email']) && isset($_POST['password']) ){

                $_POST['email'] = trim($_POST['email']);
                $_POST['password'] = trim($_POST['password']);
                //check User existance in DB, if so start session and redirect to home page.
                if(!empty($_POST['email']) && !empty($_POST['password'])){
                        $user = Doo::loadModel('User', true);
                        $user->email = $_POST['email'];
                        $user->password = $_POST['password'];
                        $user = $this->db()->find($user, array('limit'=>1));

                        if($user){
                                Doo::loadCore('session/DooSession');
                                $this->session->start();
                                unset($this->session->user);
                                $this->session->user = array(
                                                            'id'=>$user->id, 
                                                            'email'=>$user->email, 
                                                            'group'=>$user->group, 
                                                        );
                                if ($user->isAdmin()) {
                                    return Doo::conf()->APP_URL . 'index.php/admin/';
                                } else {
                                    return Doo::conf()->APP_URL . 'index.php/my/';
                                }
                        }
                }
            }
        }
        $this->data['form'] = $form->render();
        $this->data['message'] = 'User with details below not found';
        $this->renderAction('login');
    }

    public function logout(){
        $this->session->destroy();
        return Doo::conf()->APP_URL;
    }

    protected function afterLogin() {
        if ($this->session->user['group'] == 'admin') {
            return Doo::conf()->APP_URL . 'index.php/admin/';
        } else {
            return Doo::conf()->APP_URL . 'index.php/my/';
        }
    }

    private function getLoginForm() {
        Doo::loadHelper('DooForm');
        $action = Doo::conf()->APP_URL . 'index.php/login';
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements' => array(
                 'email' => array('text', array(
                     'required' => true,
                     'validators' => array('email'),
                     'label' => 'Email:',
                     'attributes' => array('class' => 'control email validate[required,email]'),
                 'element-wrapper' => 'div'
                 )),
                 'password' => array('password', array(
                     'required' => true,
                     'validators' => array('password'),
                     'label' => 'Password:',
                 'attributes' => array('class' => 'control password validate[required,length(6,10)]'),
                 'element-wrapper' => 'div'
                 )),
                 'submit' => array('submit', array(
                     'label' => "Login",
                     'attributes' => array('class' => 'buttons'),
                     'order' => 100,
                 'field-wrapper' => 'div'
                 )),

                 'register' => array('display', array(
                     'content' => '<a href=#1>Not a member?</a>',
                 'field-wrapper' => 'div'
                 ))
             )
        ));
        return $form;
    }

    private function getRegisterForm() {
        Doo::loadHelper('DooForm');
        $action = Doo::conf()->APP_URL . 'index.php/register';
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements' => array(
                 'first_name' => array('text', array(
                     'required' => true,
                     'label' => 'First Name:',
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'last_name' => array('text', array(
                     'required' => true,
                     'label' => 'Last Name:',
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'email' => array('text', array(
                     'required' => true,
                     'validators' => array('email'),
                     'label' => 'Email:',
                     'attributes' => array('class' => 'control email validate[required,email]'),
                 'element-wrapper' => 'div'
                 )),
                 'captcha' => array('captcha', array(
                     'required' => true,
                     'validators' => array('captcha'),
                     'image' => Doo::conf()->SITE_PATH.'/global/img/captcha.jpg',
                     'directory' => Doo::conf()->TMP_PATH,
                     'url' => Doo::conf()->APP_URL.'index.php/captcha/',
                     'label' => 'Email:',
                     'attributes' => array('class' => 'control textbox validate[required,captcha]'),
                 'element-wrapper' => 'div'
                 )),
                 'submit' => array('submit', array(
                     'label' => "Register",
                     'attributes' => array('class' => 'buttons'),
                     'order' => 100,
                 'field-wrapper' => 'div'
                 )),

             )
        ));
        return $form;
    }

}
?>
