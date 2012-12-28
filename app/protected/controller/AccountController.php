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

        $this->renderAction('login', 'main');
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
        $form = $this->getRegisterForm();
        if ($form->isValid($_POST)) {
            Doo::loadModel('User');
            $user = new User($_POST);
            $user->type = 'customer';
            // calculate confirm key
            $user->confirm_key = md5($user->email . '@' . Doo::conf()->SITE_ID).'@' . time();
            $user->insert();
            $this->data['message'] = 'Registered, please contact your customer service with this code:'. $user->confirm_code;
            $this->renderAction('registered');
        } else {
            $this->data['message'] = 'User with details below not found';
            $this->data['form'] = $form->render();
            $this->renderAction('registration');
        }
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
                                                            'type'=>$user->type, 
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
        if ($this->session->user['type'] == 'admin') {
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
                     'validators' => array(array('email'), array('dbExist', 'User', 'email', 'User/Password Wrong!')),
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
                 'first_name_alphabet' => array('text', array(
                     'required' => true,
                     'label' => 'First Name(pinyin):',
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'last_name_alphabet' => array('text', array(
                     'required' => true,
                     'label' => 'Last Name(pinyin):',
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'password' => array('password', array(
                     'required' => true,
                     'validators' => array('password'),
                     'label' => 'Password:',
                 'attributes' => array('class' => 'control password validate[required,length(6,10)]'),
                 'element-wrapper' => 'div'
                 )),
                 'email' => array('text', array(
                     'required' => true,
                     'validators' => array(array('email'), array('dbNotExist', 'User','email','Email exists, please choose another one!')),
                     'label' => 'Email:',
                     'attributes' => array('class' => 'control email validate[required,email]'),
                 'element-wrapper' => 'div'
                 )),
                 'phone' => array('text', array(
                     'required' => true,
                     'label' => 'Phone:',
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'qq' => array('text', array(
                     'required' => true,
                     'label' => 'QQ:',
                     'attributes' => array('class' => 'control textbox validate[required]'),
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
