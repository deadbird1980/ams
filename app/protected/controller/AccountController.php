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

        $action = "index.php/login";
        $this->data['message'] = '';
        Doo::loadHelper('DooForm');
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'login','class'=>'form'),
             'elements' => array(
                 'email' => array('text', array(
                     'required' => true,
                     'label' => 'Email:',
                 'attributes' => array('class' => 'username'),
                 'element-wrapper' => 'div'
                 )),
                 'password' => array('password', array(
                     'required' => true,
                     'label' => 'Password:',
                 'attributes' => array('class' => 'password'),
                 'element-wrapper' => 'div'
                 )),
                 'submit' => array('submit', array(
                     'label' => "Login",
                     'attributes' => array('class' => 'buttons'),
                     'order' => 100,
                 'field-wrapper' => 'div'
                 ))
             )
        ));
        $this->data['form'] = $form->render();

        $this->renderAction('login');
    }

    public function registration(){
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

}
?>
