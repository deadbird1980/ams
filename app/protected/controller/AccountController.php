<?php
class AccountController extends DooController{

    public function index(){
		session_start();
		if(isset($_SESSION['user'])){
			$data['user'] = $_SESSION['user'];
		}else{
			$data['user'] = null;
		}
		
        //labels
        $data['welcome'] = array('welcome');
        $data['baseurl'] = Doo::conf()->APP_URL;
        $data['message'] = '';
        $this->view()->render('header', $data);
        //$this->view()->render('nav', $data);

        $this->view()->render('login', $data);
    }

    public function registration(){
        //labels
        $data['welcome'] = array('welcome');
        $data['baseurl'] = Doo::conf()->APP_URL;
        $this->view()->render('registration_test', $data);
    }

    public function register(){
        if (md5(md5(md5(strtolower($_POST['captcha_code'])))) !=  @$_COOKIE['captcha']) {
          return $this->view()->render('registration', $data);
        }
        Doo::loadModel('User');
        $user = new User();
        $user->username = $_POST['username'];
        $user->pwd = $_POST['password'];
        if ($user->find(array('select'=>'id', 'limit'=>1)) != Null) {
          $data['msg'] = 'User name exists, please try another one';
          return $this->view()->render('registration', $data);
        }
        $user->insert();
        $data['msg'] = 'User registered';
        $this->view()->render('registration', $data);
    }

    public function login(){
        if(isset($_POST['username']) && isset($_POST['password']) ){

            $_POST['username'] = trim($_POST['username']);
            $_POST['password'] = trim($_POST['password']);
            //check User existance in DB, if so start session and redirect to home page.
            if(!empty($_POST['username']) && !empty($_POST['password'])){
                    $user = Doo::loadModel('User', true);
                    $user->username = $_POST['username'];
                    $user->pwd = $_POST['password'];
                    $user = $this->db()->find($user, array('limit'=>1));

                    if($user){
                            session_start();
                            unset($_SESSION['user']);
                            $_SESSION['user'] = array(
                                                        'id'=>$user->id, 
                                                        'username'=>$user->username, 
                                                        'group'=>$user->group
                                                    );
                            return Doo::conf()->APP_URL;
                    }
            }
        }

        $data['baseurl'] = Doo::conf()->APP_URL;
        $data['title'] = 'Failed to login!';
        $data['message'] = 'User with details below not found';
        $this->render('login', $data);
    }

    public function logout(){
        session_start();
        unset($_SESSION['user']);
        session_destroy();
        return Doo::conf()->APP_URL;
    }

    public function url(){
        $data['title'] = 'URL used in this demo';
        $data['content'] = 'Replace :var with your values.<br/><em>Request type */GET = You can test and visit these links.</em>';
        $data['baseurl'] = Doo::conf()->APP_URL;

        include Doo::conf()->SITE_PATH .'protected/config/routes.conf.php';
        $data['printr'] = array();
        $n = 1;
        foreach($route as $req=>$r){
            foreach($r as $rname=>$value){
                //$rname_strip = (strpos($rname, '/')===0)? substr($rname, 1, strlen($rname)) : $rname;
                $rname_strip = 'index.php'.$rname;
                $data['printr'][$n++ .strtoupper(" $req")] = '<a href="'.Doo::conf()->APP_URL.$rname_strip.'">'.$rname.'</a>';
            }
        }
        $this->view()->render('template', $data);
    }

    public function example(){
        $data['baseurl'] = Doo::conf()->APP_URL;
        $data['printr'] = file_get_contents(Doo::conf()->SITE_PATH .'protected/config/routes.conf.php');
        $this->view()->render('example', $data);
    }
}
?>
