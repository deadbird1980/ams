<?php
require_once 'BaseController.php';

class AccountController extends BaseController{

    protected $helper = 'AccountHelper';

    public function index(){
		if(isset($this->session->user)){
			$this->data['user'] = $this->session->user;
            return $this->afterLogin();
		}else{
			$this->data['user'] = null;
		}

        $this->data['message'] = '';
        Doo::loadHelper('DooForm');
        $this->data['form'] = $this->helper->getLoginForm()->render();

        $this->renderAction('login', 'main');
    }

    public function captcha() {
        $f = $this->params['file'];
        $this->setContentType('jpg');
        echo file_get_contents(Doo::conf()->TMP_PATH.'/'.$f);
    }

    public function registration(){
        $this->data['form'] = $this->helper->getRegisterForm()->render();
        $this->renderAction('registration');
    }

    public function resetPassword(){

        if ($this->isPost()) {
            $this->params['confirm_code'] = $_POST['confirm_code'];
            $form = $this->helper->getResetPasswordForm();
            if ($form->isValid($_POST)) {
                Doo::loadModel('User');
                $u = new User();
                $u->confirm_code = $_POST['confirm_code'];
                $u = $this->db()->find($u, array('limit'=>1));
                $u->resetPassword ($_POST['password']);
                $this->data['message'] = $this->t('updated');
                $this->renderAction('registered');
            }
        } else if (isset($_GET['confirm_code'])) {
            $this->params['confirm_code'] = $_GET['confirm_code'];
            $form = $this->helper->getResetPasswordForm();
        } else {
            return array('not available', 404);
        }
        $this->data['form'] = $form->render();
        $this->renderAction('reset_password');
    }
    public function forgottenPassword(){
        $form = $this->helper->getForgottenPasswordForm();
        if ($this->isPost()) {
            if ($form->isValid($_POST)) {
                Doo::loadModel('User');
                $user = new User();
                $user = $user->getByEmail_first($_POST['email']);
                $user->resetConfirmCode();
                $this->data['url'] = Doo::conf()->APP_URL.'reset_password?confirm_code='.$user->confirm_code;
                $this->data['forgotten_password_user'] = $user;
                if ($this->notifyUser($user, $this->t('forgotten_password'), 'forgotten_password')) {
                    $this->data['message'] = $this->t('forgotten_password_email_sent');
                } else {
                    $this->data['message'] = $this->t('fail_to_send_email');
                }
            }
        }
        $this->data['form'] = $form->render();
        $this->renderAction('forgotten_password');
    }

    public function register(){
        $form = $this->helper->getRegisterForm();
        if ($form->isValid($_POST)) {
            Doo::loadModel('User');
            $user = User::register($_POST);
            $this->data['message'] = $this->t('registered'). $user->confirm_code;
            // send mail to the register
            $this->data['registered_user'] = $user;
            $this->notifyUser($user, $this->t('registered'), 'registered');
            $this->renderAction('registered');
        } else {
            $this->data['message'] = 'User with details below not found';
            $this->data['form'] = $form->render();
            $this->renderAction('registration');
        }
    }


    public function login(){
        $form = $this->helper->getLoginForm();
        $this->data['message'] = $this->t('wrong_email_password');
        if ($form->isValid($_POST)) {
            $_POST['email'] = trim($_POST['email']);
            $_POST['password'] = trim($_POST['password']);
            if(!empty($_POST['email']) && !empty($_POST['password'])){
                $user = Doo::loadModel('User', true);
                $user = $user->getByEmail_first($_POST['email']);
                if($user && $user->isActive() && ($this->isDev() || $user->confirmPassword($_POST['password']))) {
                    if ($user->isRegistered()) {
                        $this->data['message'] = $this->t('not_activated');
                    } else {
                        $this->auth->setData($user->email, $user->type);
                        $this->auth->user = $user;
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
        $this->renderAction('login');
    }

    public function logout(){
        $this->auth->finalize();
        return Doo::conf()->APP_URL;
    }

    protected function afterLogin() {
        if ($this->session->user['type'] == 'admin') {
            return Doo::conf()->APP_URL . 'index.php/admin/';
        } else {
            return Doo::conf()->APP_URL . 'index.php/my/';
        }
    }
}
?>
