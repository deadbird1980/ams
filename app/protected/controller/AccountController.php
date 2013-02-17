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

    public function forgottenPassword(){
        $form = $this->helper->getForgottenPasswordForm();
        if ($this->isPost()) {
            if ($form->isValid($_POST)) {
                Doo::loadHelper('DooMailer');
                $mail = new DooMailer();
                $mail->addTo($_POST['email']);
                $mail->setSubject($this->t('forgotten_password'));
                $mail->setBodyText("This is plain text body");
                $mail->setFrom('noreply@ams.com', 'no reply');
                if ($mail->send()) {
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
            $user = new User($_POST);
            $user->type = 'customer';
            $user->status = 'registered';
            // calculate confirm key
            $user->confirm_code = md5($user->email . '@' . Doo::conf()->SITE_ID.'@' . time());
            $user->insert();
            $this->data['message'] = $this->t('registered'). $user->confirm_code;
            // send mail to the register
            Doo::loadHelper('DooMailer');
            $mail = new DooMailer();
            $mail->addTo($_POST['email']);
            $mail->setSubject($this->t('forgotten_password'));
            $mail->setBodyText($this->data['message']);
            $mail->setFrom('noreply@ams.com', 'no reply');
            if ($mail->send()) {
            }
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
            if(isset($_POST['email']) && isset($_POST['password']) ){

                $_POST['email'] = trim($_POST['email']);
                $_POST['password'] = trim($_POST['password']);
                //check User existance in DB, if so start session and redirect to home page.
                if(!empty($_POST['email']) && !empty($_POST['password'])){
                        $user = Doo::loadModel('User', true);
                        $user->email = $_POST['email'];
                        $user->password = $_POST['password'];
                        if (Doo::conf()->APP_MODE == 'dev') {
                            $user = $user->getByEmail_first($_POST['email']);
                        } else {
                            $user = $this->db()->find($user, array('limit'=>1));
                        }

                        if($user){
                            if ($user->isRegistered()) {
                                $this->data['message'] = $this->t('not_activated');
                            } else {
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
        }
        $this->data['form'] = $form->render();
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
}
?>
