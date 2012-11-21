<?php
require_once 'BaseController.php';

class AccountController extends BaseController{

    public function index(){
		session_start();
		if(isset($_SESSION['user'])){
			$this->data['user'] = $_SESSION['user'];
		}else{
			$this->data['user'] = null;
		}

        $this->data['message'] = '';

        $this->renderAction('login');
    }

    public function registration(){
        $this->renderAction('registration_test');
    }

    public function register(){
        if (md5(md5(md5(strtolower($_POST['captcha_code'])))) !=  @$_COOKIE['captcha']) {
          $this->data['message'] = 'Please input right string from the image';
          return $this->renderAction('registration');
        }
        Doo::loadModel('User');
        $user = new User();
        $user->username = $_POST['username'];
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
        if(isset($_POST['username']) && isset($_POST['password']) ){

            $_POST['username'] = trim($_POST['username']);
            $_POST['password'] = trim($_POST['password']);
            //check User existance in DB, if so start session and redirect to home page.
            if(!empty($_POST['username']) && !empty($_POST['password'])){
                    $user = Doo::loadModel('User', true);
                    $user->username = $_POST['username'];
                    $user->password = $_POST['password'];
                    $user = $this->db()->find($user, array('limit'=>1));

                    if($user){
                            session_start();
                            unset($_SESSION['user']);
                            $_SESSION['user'] = array(
                                                        'id'=>$user->id, 
                                                        'username'=>$user->username, 
                                                        'group'=>'admin', 
                                                    );
                            return Doo::conf()->APP_URL . 'index.php/admin/';
                    }
            }
        }

        $this->data['message'] = 'User with details below not found';
        $this->renderAction('login');
    }

    public function logout(){
        session_start();
        unset($_SESSION['user']);
        session_destroy();
        return Doo::conf()->APP_URL;
    }

}
?>
