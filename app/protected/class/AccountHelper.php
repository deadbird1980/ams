<?php
class AccountHelper extends Helper {

    public function getLoginForm() {
        Doo::loadHelper('DooForm');
        $action = DooUrlBuilder::url2('AccountController', 'login', null, true);
        $elements = array(
                 'token' => array('hidden', array(
                     'required' => true,
                     'value' => $this->controller->getAuthenticityToken(),
                     'validators' => array(array('custom', array($this->controller,'isValidToken'))),
                 )),
                 'email' => array('text', array(
                     'required' => true,
                     'validators' => array(array('email'), array('dbExist', 'user', 'email', 'User/Password Wrong!')),
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
                     'content' => "<a href=".DooUrlBuilder::url2('AccountController', 'registration', null, true).">{$this->t('register')}</a>&nbsp;&nbsp;&nbsp;<a href=".DooUrlBuilder::url2('AccountController', 'forgottenPassword', null, true).">{$this->t('forgotten_password')}</a>",
                     'attributes' => array('class'=>'link'),
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

    public function getRegisterForm() {
        $action = Doo::conf()->APP_URL . 'index.php/register';
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements' => array(
                 'token' => array('hidden', array(
                     'required' => true,
                     'value' => $this->controller->getAuthenticityToken(),
                     'validators' => array(array('custom', array($this->controller,'isValidToken'))),
                 )),
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
                     'validators' => array(array('email'), array('dbNotExist', 'user','email','Email exists, please choose another one!')),
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

    public function getForgottenPasswordForm() {
        $action = Doo::conf()->APP_URL . 'index.php/forgotten_password';
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements' => array(
                 'token' => array('hidden', array(
                     'required' => true,
                     'value' => $this->controller->getAuthenticityToken(),
                     'validators' => array(array('custom', array($this->controller,'isValidToken'))),
                 )),
                 'email' => array('text', array(
                     'required' => true,
                     'validators' => array(array('email'), array('dbExist', 'user','email','Email not exists, please choose another one!')),
                     'label' => $this->t('email'),
                     'attributes' => array('class' => 'control email validate[required,email]'),
                 'element-wrapper' => 'div'
                 )),
                 'submit' => array('submit', array(
                     'label' => $this->t('submit'),
                     'attributes' => array('class' => 'buttons'),
                     'order' => 100,
                 'field-wrapper' => 'div'
                 )),

             )
        ));
        return $form;
    }

    public function getResetPasswordForm() {
        $action = Doo::conf()->APP_URL . 'index.php/reset_password';
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements' => array(
                 'token' => array('hidden', array(
                     'required' => true,
                     'value' => $this->controller->getAuthenticityToken(),
                     'validators' => array(array('custom', array($this->controller,'isValidToken'))),
                 )),
                 'confirm_code' => array('hidden', array(
                     'required' => true,
                     'value' => $this->controller->params['confirm_code'],
                     'validators' => array(array('confirm_code'), array('dbExist', 'user', 'confirm_code', $this->t('confirm_code error'))),
                 )),
                 'password' => array('password', array(
                     'required' => true,
                     'validators' => array('password'),
                     'label' => $this->t('password'),
                     'attributes' => array('class' => 'control password validate[required,length(6,10)]'),
                     'element-wrapper' => 'div'
                 )),
                 'confirm_password' => array('password', array(
                     'required' => true,
                     'validators' => array('password'),
                     'label' => $this->t('confirm_password'),
                     'attributes' => array('class' => 'control password validate[required,length(6,10),compare(password-element)]'),
                     'element-wrapper' => 'div'
                 )),
                 'submit' => array('submit', array(
                     'label' => $this->t('submit'),
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
