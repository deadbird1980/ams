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
            $user->status = 'registered';
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
                        if (Doo::conf()->APP_MODE == 'dev') {
                            $user = $user->getByEmail_first($_POST['email']);
                        } else {
                            $user = $this->db()->find($user, array('limit'=>1));
                        }

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
        Doo::loadHelper('DooUrlBuilder');
        $action = DooUrlBuilder::url2('AccountController', 'login', null, true);
        $elements = array(
                 'email' => array('text', array(
                     'required' => true,
                     'validators' => array(array('email'), array('dbExist', 'User', 'email', 'User/Password Wrong!')),
                     'label' => $this->t('email'),
                     'attributes' => array('class' => 'control email validate[required,email]'),
                 'element-wrapper' => 'div'
                 )),
                 'password' => array('password', array(
                     'required' => true,
                     'validators' => array('password'),
                     'label' => $this->t('password'),
                 'attributes' => array('class' => 'control password validate[required,length(6,10)]'),
                 'element-wrapper' => 'div'
                 )),
                 'submit' => array('submit', array(
                     'label' => $this->t('login'),
                     'attributes' => array('class' => 'buttons'),
                     'order' => 100,
                 'field-wrapper' => 'div'
                 )),

                 'register' => array('display', array(
                     'content' => "<a href=".DooUrlBuilder::url2('AccountController', 'registration', null, true).">{$this->t('register')}</a>",
                 'field-wrapper' => 'div'
                 ))
             );
        if (Doo::conf()->APP_MODE == 'dev') {
            $elements['email'][0] = 'select';
            Doo::loadModel('User');
            $user = new User();
            $options = array(''=>'');
            $users = $user->find();
            foreach($users as $u) {
                $options[$u->email] = $u->email;
            }
            $elements['email'][1]['multioptions'] = $options;
            $elements['password'] = array('hidden', array(
                                 'value'=>'password'
                                 ));
        }

        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements' => $elements
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
                     'label' => $this->t('first_name'),
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'last_name' => array('text', array(
                     'required' => true,
                     'label' => $this->t('last_name'),
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'first_name_alphabet' => array('text', array(
                     'required' => true,
                     'label' => $this->t('first_name_pinyin'),
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'last_name_alphabet' => array('text', array(
                     'required' => true,
                     'label' => $this->t('last_name_pinyin'),
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'password' => array('password', array(
                     'required' => true,
                     'validators' => array('password'),
                     'label' => $this->t('password'),
                 'attributes' => array('class' => 'control password validate[required,length(6,10)]'),
                 'element-wrapper' => 'div'
                 )),
                 'email' => array('text', array(
                     'required' => true,
                     'validators' => array(array('email'), array('dbNotExist', 'User','email','Email exists, please choose another one!')),
                     'label' => $this->t('email'),
                     'attributes' => array('class' => 'control email validate[required,email]'),
                 'element-wrapper' => 'div'
                 )),
                 'phone' => array('text', array(
                     'required' => true,
                     'label' => $this->t('phone'),
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'qq' => array('text', array(
                     'required' => true,
                     'label' => $this->t('qq'),
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'submit' => array('submit', array(
                     'label' => $this->t('register'),
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
