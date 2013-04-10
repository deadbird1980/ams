<?php
class UserHelper extends Helper {

    public function getUserForm($u) {
        Doo::loadHelper('DooForm');
        if ($u->id) {
            $action = Doo::conf()->APP_URL . 'index.php/'.$this->controller->getRange().'/users/'.$u->id;
        } else {
            $action = Doo::conf()->APP_URL . 'index.php/'.$this->controller->getRange().'/users/save';
        }
        $elements = array(
                 'token' => array('hidden', array(
                     'required' => true,
                     'value' => $this->controller->getAuthenticityToken(),
                     'validators' => array(array('custom', array($this->controller,'isValidToken'))),
                 )),
                 'first_name' => array('text', array(
                     'required' => true,
                     'label' => $this->t('first_name'),
                     'value' => $u->first_name,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'last_name' => array('text', array(
                     'required' => true,
                     'label' => $this->t('last_name'),
                     'value' => $u->last_name,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'first_name_alphabet' => array('text', array(
                     'required' => true,
                     'label' => $this->t('first_name_pinyin'),
                     'value' => $u->first_name_alphabet,
                     'attributes' => array('class' => 'control textbox validate[required,alphabet()]'),
                 'element-wrapper' => 'div'
                 )),
                 'last_name_alphabet' => array('text', array(
                     'required' => true,
                     'label' => $this->t('last_name_pinyin'),
                     'value' => $u->last_name_alphabet,
                     'attributes' => array('class' => 'control textbox validate[required,alphabet()]'),
                 'element-wrapper' => 'div'
                 )),
                 'password' => array('password', array(
                     'required' => true,
                     'validators' => array('password'),
                     'label' => $this->t('password'),
                     'value' => $u->password,
                 'attributes' => array('class' => 'control password validate[required,length(8,)]'),
                 'element-wrapper' => 'div'
                 )),
                 'email' => array('text', array(
                     'required' => true,
                     'validators' => array(array('email')),
                     'label' => $this->t('email'),
                     'value' => $u->email,
                     'attributes' => array('class' => 'control email validate[required,email]'),
                 'element-wrapper' => 'div'
                 )),
                 'phone' => array('text', array(
                     'required' => true,
                     'label' => $this->t('phone'),
                     'value' => $u->phone,
                     'attributes' => array('class' => 'control textbox validate[number()]'),
                 'element-wrapper' => 'div'
                 )),
                 'qq' => array('text', array(
                     'required' => true,
                     'label' => $this->t('qq'),
                     'value' => $u->qq,
                     'attributes' => array('class' => 'control textbox validate[number()]'),
                 'element-wrapper' => 'div'
                 )),
                 'confirm_code' => array('text', array(
                     'required' => true,
                     'label' => $this->t('confirm_code'),
                     'value' => $u->confirm_code,
                     'attributes' => array('class' => 'control textbox'),
                     'element-wrapper' => 'div'
                 )),
                 'status' => array('select', array(
                     'required' => true,
                     'multioptions' => array('registered'=>'注册', 'active'=>'激活', 'obsolete'=>'过期'),
                     'label' => $this->t('status'),
                     'value' => $u->status,
                     'attributes' => array('class' => 'control type validate[required]'),
                     'element-wrapper' => 'div'
                 )),
             );
        if ($this->controller->isAdmin()) {
        $elements['type'] = array('select', array(
                     'required' => true,
                     'multioptions' => array('' => '' , 'customer'=>'客户', 'counselor'=>'咨询员', 'executor'=>'执行员', 'admin'=>'管理员'),
                     'label' => 'Type:',
                     'value' => $u->type,
                     'attributes' => array('class' => 'control type validate[required]'),
                     'element-wrapper' => 'div'
                 ));
        }
        $elements['submit'] = array('submit', array(
                     'label' => "Save",
                     'attributes' => array('class' => 'buttons'),
                     'order' => 100,
                 'field-wrapper' => 'div'
                 ));
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements' => $elements
        ));
        return $form;
    }

    public function getActivateUserForm() {
        Doo::loadHelper('DooForm');
        Doo::loadHelper('DooUrlBuilder');
        $action = DooUrlBuilder::url2('UserController', 'activate', null, true);
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
                 'confirm_code' => array('text', array(
                     'validators' => array(array('dbExist', 'user', 'confirm_code', 'The confirm code does not exist!')),
                     'label' => 'Confirm Code:',
                     'required' => true,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'submit' => array('submit', array(
                     'label' => "激活",
                     'attributes' => array('class' => 'buttons'),
                     'order' => 100,
                 'field-wrapper' => 'div'
                 ))
             )
        ));
        return $form;
    }
}
?>
