<?php
class ApplicationHelper {
    private $controller;
    public function __construct($controller) {
        Doo::loadHelper('DooForm');
        Doo::loadHelper('DooUrlBuilder');
        $this->controller = $controller;
    }

    private function t($str) {
        return $this->controller->t($str);
    }

    public function getApplicationForm($app) {
        if ($app->isVisa()) {
            $form = $this->getEuropeVisaForm($app);
        } else {
            $form = $this->getSchoolForm($app);
        }
        return $form;
    }

    public function getConfirmApplicationForm() {
        $app = $this->data['application'];
        $action = DooUrlBuilder::url2('MyController', 'submitApplication', array('id'=>$app->id), true);
        Doo::loadModel('VisaApplication');
        $visaapp = new VisaApplication();
        $visaapp = $visaapp->getById_first($app->id);
        $elements =  array(
                 'type' => array('hidden', array(
                     'value' => $app->type,
                 )),
                 'start_date' => array('display', array(
                     'label' => $this->t('start_date'),
                     'content' => $visaapp->start_date,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'passport' => array('display', array(
                     'content' => '护照信息:',
                 )),
                 'passport_no' => array('display', array(
                     'label' => $this->t('passport_no'),
                     'content' => $visaapp->passport_no,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'passport_name' => array('display', array(
                     'label' => $this->t('passport_name'),
                     'content' => $visaapp->passport_name,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'birthday' => array('display', array(
                     'label' => $this->t('birthday'),
                     'content' => $visaapp->birthday,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'passport_start_date' => array('display', array(
                     'label' => $this->t('start_date'),
                     'content' => $visaapp->passport_start_date,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'passport_end_date' => array('display', array(
                     'label' => $this->t('end_date'),
                     'content' => $visaapp->passport_end_date,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'visa' => array('display', array(
                     'content' => '当前签证状态:',
                 )),
                 'address' => array('display', array(
                     'label' => $this->t('uk_address'),
                     'content' => $visaapp->address,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'visa_start_date' => array('display', array(
                     'label' => $this->t('start_date'),
                     'content' => $visaapp->visa_start_date,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'visa_end_date' => array('display', array(
                     'label' => $this->t('end_date'),
                     'content' => $visaapp->visa_end_date,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'work' => array('display', array(
                     'content' => '目前工作学习状况:',
                 )),
                 'organization' => array('display', array(
                     'label' => $this->t('organization'),
                     'content' => $visaapp->organization,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )));
        if (!$app->isSubmitted()) {
            $elements['submit'] = array('submit', array(
                     'label' => $this->t('submit'),
                     'attributes' => array('class' => 'buttons'),
                     'order' => 100,
                 'field-wrapper' => 'div'
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

    public function getFilesForm() {
        Doo::loadHelper('DooForm');
        $action = Doo::conf()->APP_URL . 'index.php/my/users/activate';
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements' => array(
                 'files' => array('display', array(
                     'content' => '文件:',
                 )),
                 'attachment' => array('file', array(
                     'required' => true,
                     'attributes' => array('class' => 'control multi max-15 accept-png|jpg validate[filesize]'),
                 'field-wrapper' => 'div'
                 )),
                 'submit' => array('submit', array(
                     'label' => "下一步",
                     'attributes' => array('class' => 'buttons'),
                     'order' => 100,
                 'field-wrapper' => 'div'
                 ))
             )
        ));
        return $form;
    }

    public function getEuropeVisaForm($app) {
        Doo::loadHelper('DooForm');
        Doo::loadHelper('DooUrlBuilder');
        if ($app && $app->id) {
            $action = DooUrlBuilder::url2('MyController', 'editApplication', array('id'=>$app->id), true);
        } else {
            $action = DooUrlBuilder::url2('MyController', 'apply', array('type'=>$app->type), true);
        }
        Doo::loadModel('VisaApplication');
        $visaapp = $app->createDetailApplication();
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements' => array(
                 'type' => array('hidden', array(
                     'value' => $app->type,
                 )),
                 'start_date' => array('text', array(
                     'label' => $this->t('start_date'),
                     'required' => true,
                     'value' => $visaapp->start_date,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'passport' => array('display', array(
                     'content' => $this->t('passport_information'),
                 )),
                 'passport_no' => array('text', array(
                     'label' => $this->t('passport_no'),
                     'required' => true,
                     'value' => $visaapp->passport_no,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'passport_name' => array('text', array(
                     'label' => $this->t('passport_name'),
                     'required' => true,
                     'value' => $visaapp->passport_name,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'birthday' => array('text', array(
                     'label' => $this->t('birthday'),
                     'required' => true,
                     'value' => $visaapp->birthday,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'passport_start_date' => array('text', array(
                     'label' => $this->t('start_date'),
                     'required' => true,
                     'value' => $visaapp->passport_start_date,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'passport_end_date' => array('text', array(
                     'label' => $this->t('end_date'),
                     'required' => true,
                     'value' => $visaapp->passport_end_date,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'visa' => array('display', array(
                     'content' => $this->t('visa_status'),
                 )),
                 'address' => array('text', array(
                     'label' => $this->t('uk_address'),
                     'required' => true,
                     'value' => $visaapp->address,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'visa_start_date' => array('text', array(
                     'label' => $this->t('start_date'),
                     'required' => true,
                     'value' => $visaapp->visa_start_date,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'visa_end_date' => array('text', array(
                     'label' => $this->t('end_date'),
                     'required' => true,
                     'value' => $visaapp->visa_end_date,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'work' => array('display', array(
                     'content' => $this->t('work_study_information'),
                 )),
                 'organization' => array('text', array(
                     'label' => $this->t('organization'),
                     'required' => true,
                     'value' => $visaapp->organization,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'submit' => array('submit', array(
                     'label' => $this->t('next'),
                     'attributes' => array('class' => 'buttons'),
                     'order' => 100,
                 'field-wrapper' => 'div'
                 ))
             )
        ));
        return $form;
    }

    public function getSchoolForm($app) {
        if ($app && $app->id) {
            $action = DooUrlBuilder::url2('MyController', 'editApplication', array('id'=>$app->id), true);
        } else {
            $action = DooUrlBuilder::url2('MyController', 'apply', array('type'=>$app->type), true);
        }
        $visaapp = $app->createDetailApplication();
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements' => array(
                 'type' => array('hidden', array(
                     'value' => $app->type,
                 )),
                 'passport' => array('display', array(
                     'content' => $this->t('passport_information'),
                 )),
                 'passport_no' => array('text', array(
                     'label' => $this->t('passport_no'),
                     'required' => true,
                     'value' => $visaapp->passport_no,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'passport_name' => array('text', array(
                     'label' => $this->t('passport_name'),
                     'required' => true,
                     'value' => $visaapp->passport_name,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'birthday' => array('text', array(
                     'label' => $this->t('birthday'),
                     'required' => true,
                     'value' => $visaapp->birthday,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'passport_start_date' => array('text', array(
                     'label' => $this->t('start_date'),
                     'required' => true,
                     'value' => $visaapp->passport_start_date,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'passport_end_date' => array('text', array(
                     'label' => $this->t('end_date'),
                     'required' => true,
                     'value' => $visaapp->passport_end_date,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'address' => array('text', array(
                     'label' => $this->t('cn_address'),
                     'required' => true,
                     'value' => $visaapp->address,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'submit' => array('submit', array(
                     'label' => $this->t('next'),
                     'attributes' => array('class' => 'buttons'),
                     'order' => 100,
                 'field-wrapper' => 'div'
                 ))
             )
        ));
        return $form;
    }

    public function getTypeForm() {
        Doo::loadHelper('DooForm');
        Doo::loadHelper('DooUrlBuilder');
        $action = DooUrlBuilder::url2('ApplicationController', 'create', array('user_id'=>$this->controller->params['user_id']), true);
        $options = array('' => '----------',
                         'visa_europe' => $this->t('visa_europe'),
                         '-' => '----'.$this->t('europe_visa').'----',
                         'visa_t1' => $this->t('visa_t1'),
                         'visa_t2' => $this->t('visa_t2'),
                         'visa_t4' => $this->t('visa_t4'),
                         'visa_other' => $this->t('visa_other'),
                         '--' => '----'.$this->t('school').'----',
                         'language' => $this->t('language'),
                         'gcse' => $this->t('gcse'),
                         'a-level' => $this->t('a-level'),
                         'pre-bachelor' => $this->t('pre-bachelor'),
                         'bachelor' => $this->t('bachelor'),
                         'pre-master' => $this->t('pre-master'),
                         'master' => $this->t('master'),
                         'doctor' => $this->t('doctor'),
                        );
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements' => array(
                 'type' => array('select', array(
                     'label' => $this->t('type'),
                     'required' => true,
                     'value' => array(''),
                     'multioptions' => $options,
                     'attributes' => array('class' => 'control textbox validate[required,not_empty]'),
                 'element-wrapper' => 'div'
                 )),
                 'submit' => array('submit', array(
                     'label' => $this->t('create'),
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
