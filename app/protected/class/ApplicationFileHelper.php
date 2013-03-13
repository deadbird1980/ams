<?php
class ApplicationFileHelper extends Helper {

    public function getApplicationFileForm($appFile) {
        Doo::loadHelper('DooForm');
        Doo::loadHelper('DooUrlBuilder');
        if ($appFile->id) {
          $action = Doo::conf()->APP_URL . "index.php/admin/application_files/{$appFile->id}";
        } else {
          $action = Doo::conf()->APP_URL . "index.php/admin/application_files/save";
        }
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
                 'token' => array('hidden', array(
                     'required' => true,
                     'value' => $this->controller->getAuthenticityToken(),
                     'validators' => array(array('custom', array($this->controller,'isValidToken'))),
                 )),
                 'application_type' => array('select', array(
                     'label' => $this->t('type'),
                     'required' => true,
                     'value' => $appFile->application_type,
                     'multioptions' => $options,
                     'attributes' => array('class' => 'control textbox validate[required,not_empty]'),
                 'element-wrapper' => 'div'
                 )),
                 'name' => array('text', array(
                     'label' => $this->t('name'),
                     'required' => true,
                     'value' => $appFile->name,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'mandatory' => array('select', array(
                     'required' => true,
                     'multioptions' => array('1'=>$this->t('yes'), '0'=>$this->t('no')),
                     'label' => $this->t('mandatory'),
                     'value' => $appFile->mandatory,
                     'attributes' => array('class' => 'control type validate[required]'),
                     'element-wrapper' => 'div'
                 )),
                 'submit' => array('submit', array(
                     'label' => $this->t('submit'),
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
