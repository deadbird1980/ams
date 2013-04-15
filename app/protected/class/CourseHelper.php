<?php
class CourseHelper extends Helper {

    public function getApplicationTypeForm($app) {
        Doo::loadHelper('DooForm');
        Doo::loadHelper('DooUrlBuilder');
        $action = DooUrlBuilder::url2('ApplicationController', 'editType', array('id'=>$app->id), true);
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
                     'value' => $app->type,
                     'multioptions' => $options,
                     'attributes' => array('class' => 'control textbox validate[required,not_empty]'),
                 'element-wrapper' => 'div'
                 )),
                 'school' => array('text', array(
                     'label' => $this->t('school'),
                     'required' => false,
                     'value' => $app->getDetail()->school,
                     'attributes' => array('class' => 'control textbox'),
                     'element-wrapper' => 'div',
                 )),
                 'subject' => array('text', array(
                     'label' => $this->t('subject'),
                     'required' => false,
                     'value' => $app->getDetail()->subject,
                     'attributes' => array('class' => 'control textbox'),
                     'element-wrapper' => 'div',
                 )),
                 'course' => array('text', array(
                     'label' => $this->t('course'),
                     'required' => false,
                     'value' => $app->getDetail()->course,
                     'attributes' => array('class' => 'control textbox'),
                     'element-wrapper' => 'div',
                 )),
                 'submit' => array('submit', array(
                     'label' => $this->t('update'),
                     'attributes' => array('class' => 'buttons'),
                     'order' => 100,
                 'field-wrapper' => 'div'
                 ))
             )
        ));
        $form->addDisplayGroup('group', array('school','course', 'subject'));
        return $form;
    }

    public function getCourseStatusForm($app) {
        Doo::loadHelper('DooForm');
        Doo::loadHelper('DooUrlBuilder');
        $action = DooUrlBuilder::url2('CourseController', 'status', array('id'=>$app->id), true);
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
                 'school' => array('display', array(
                     'label' => $this->t('school'),
                     'content' => "{$app->school}  {$app->subject}  {$app->course}",
                     'element-wrapper' => 'div'
                 )),
                 'status' => array('select', array(
                     'required' => true,
                     'multioptions' => array(CourseApplication::SUBMITTED=>$this->t(CourseApplication::SUBMITTED),
                                             CourseApplication::CONFIRMED=>$this->t(CourseApplication::CONFIRMED),
                                             CourseApplication::SENT=>$this->t(CourseApplication::SENT),
                                             CourseApplication::REPLIED=>$this->t(CourseApplication::REPLIED),
                                             CourseApplication::DONE=>$this->t(CourseApplication::DONE),),
                     'label' => $this->t('status'),
                     'value' => $app->status,
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

    public function getCourseReplyForm($app) {
        Doo::loadHelper('DooForm');
        Doo::loadHelper('DooUrlBuilder');
        $action = DooUrlBuilder::url2('CourseController', 'reply', array('id'=>$app->id), true);
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
                 'school' => array('display', array(
                     'label' => $this->t('school'),
                     'content' => "{$app->school}  {$app->subject}  {$app->course}",
                     'element-wrapper' => 'div'
                 )),
                 'result' => array('select', array(
                     'required' => true,
                     'multioptions' => array(CourseApplication::REFUSED=>$this->t(CourseApplication::REFUSED),
                                             CourseApplication::CONDITION_APPROVED=>$this->t(CourseApplication::CONDITION_APPROVED),
                                             CourseApplication::APPROVED=>$this->t(CourseApplication::APPROVED),),
                     'label' => $this->t('status'),
                     'value' => $app->status,
                     'attributes' => array('class' => 'control validate[required]'),
                     'element-wrapper' => 'div'
                 )),
                 's1'=> array('display', array(
                     'content' => '&nbsp;&nbsp',
                     'attributes' => array('class' => 'hidden'),
                 )),
                 'add_link'=> array('display', array(
                     'content' => '&nbsp;&nbsp<a id="add-file" href="#">'.$this->t('add_file').'</a>',
                     'attributes' => array('class' => 'hidden'),
                 )),
                 'file' => array('file', array(
                     'label' => $this->t('file'),
                     'required' => false,
                     'value' => '',
                     'attributes' => array('name'=>'files[]', 'class' => 'control textbox hidden'),
                     'element-wrapper' => 'div',
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

    public function getCourseFinishForm($app) {
        Doo::loadHelper('DooForm');
        Doo::loadHelper('DooUrlBuilder');
        $action = DooUrlBuilder::url2('CourseController', 'finish', array('id'=>$app->id), true);
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
                 'school' => array('display', array(
                     'label' => $this->t('school'),
                     'content' => "{$app->school}  {$app->subject}  {$app->course}",
                     'element-wrapper' => 'div'
                 )),
                 'result' => array('select', array(
                     'required' => true,
                     'multioptions' => array(CourseApplication::REFUSED=>$this->t(CourseApplication::REFUSED),
                                             CourseApplication::APPROVED=>$this->t(CourseApplication::APPROVED),),
                     'label' => $this->t('status'),
                     'value' => $app->status,
                     'attributes' => array('class' => 'control type validate[required]'),
                     'element-wrapper' => 'div'
                 )),
                 's1'=> array('display', array(
                     'content' => '&nbsp;&nbsp',
                     'attributes' => array('class' => 'hidden'),
                 )),
                 'add_link'=> array('display', array(
                     'content' => '&nbsp;&nbsp<a id="add-file" href="#">'.$this->t('add_file').'</a>',
                     'attributes' => array('class' => 'hidden'),
                 )),
                 'file' => array('file', array(
                     'label' => $this->t('file'),
                     'required' => false,
                     'value' => '',
                     'attributes' => array('name'=>'files[]', 'class' => 'control textbox hidden'),
                     'element-wrapper' => 'div',
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

    public function getCourseChooseForm($app) {
        Doo::loadHelper('DooForm');
        Doo::loadHelper('DooUrlBuilder');
        $action = DooUrlBuilder::url2('CourseController', 'choose', array('id'=>$app->id), true);
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
                 'school' => array('display', array(
                     'label' => $this->t('school'),
                     'content' => "{$app->school}  {$app->subject}  {$app->course}",
                     'element-wrapper' => 'div'
                 )),
                 's1'=> array('display', array(
                     'content' => '&nbsp;&nbsp',
                     'attributes' => array('class' => 'hidden'),
                 )),
                 'add_link'=> array('display', array(
                     'content' => '&nbsp;&nbsp<a id="add-file" href="#">'.$this->t('add_file').'</a>',
                     'attributes' => array('class' => 'hidden'),
                 )),
                 'file' => array('file', array(
                     'label' => $this->t('file'),
                     'required' => false,
                     'value' => '',
                     'attributes' => array('name'=>'files[]', 'class' => 'control textbox hidden'),
                     'element-wrapper' => 'div',
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

    public function getCourseForm($app) {
        return $this->getCourseViewForm($app);
    }

    public function getCourseEditForm($app) {
        if ($app && $app->id) {
            $action = DooUrlBuilder::url2('MyController', 'editApplication', array('id'=>$app->id), true);
        } else {
            $action = DooUrlBuilder::url2('MyController', 'apply', array('type'=>$app->type), true);
        }
        $elements = array(
                 'token' => array('hidden', array(
                     'required' => true,
                     'value' => $this->controller->getAuthenticityToken(),
                     'validators' => array(array('custom', array($this->controller,'isValidToken'))),
                 )),
                 'school' => array('text', array(
                     'label' => $this->t('school'),
                     'required' => false,
                     'value' => $app->school,
                     'attributes' => array('class' => 'control textbox'),
                     'element-wrapper' => 'div',
                 )),
                 'subject' => array('text', array(
                     'label' => $this->t('subject'),
                     'required' => false,
                     'value' => $app->subject,
                     'attributes' => array('class' => 'control textbox'),
                     'element-wrapper' => 'div',
                 )),
                 'course' => array('text', array(
                     'label' => $this->t('course'),
                     'required' => false,
                     'value' => $app->course,
                     'attributes' => array('class' => 'control textbox'),
                     'element-wrapper' => 'div',
                 )),
             );
        $i = 1;
        $elements['submit'] = array('submit', array(
                     'label' => $this->t('next'),
                     'attributes' => array('class' => 'buttons'),
                     'order' => 100,
                 'field-wrapper' => 'div'
                 ));
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements'=>$elements
        ));
        return $form;
    }

    public function getCourseViewForm($app) {
        if ($app && $app->id) {
            $action = DooUrlBuilder::url2('MyController', 'editApplication', array('id'=>$app->id), true);
        } else {
            $action = DooUrlBuilder::url2('MyController', 'apply', array('type'=>$app->type), true);
        }
        $elements = array(
                 'token' => array('hidden', array(
                     'required' => true,
                     'value' => $this->controller->getAuthenticityToken(),
                     'validators' => array(array('custom', array($this->controller,'isValidToken'))),
                 )),
                 'school' => array('display', array(
                     'label' => $this->t('school'),
                     'content' => $app->school,
                     'element-wrapper' => 'div',
                 )),
                 'subject' => array('display', array(
                     'label' => $this->t('subject'),
                     'content' => $app->subject,
                     'element-wrapper' => 'div',
                 )),
                 'course' => array('display', array(
                     'label' => $this->t('course'),
                     'content' => $app->course,
                     'element-wrapper' => 'div',
                 )),
             );
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements'=>$elements
        ));
        return $form;
    }

    public function getCourseConfirmForm($app) {
        if ($app && $app->id) {
            $action = DooUrlBuilder::url2('CourseController', 'reconfirm', array('id'=>$app->id), true);
        }
        $elements = array(
                 'token' => array('hidden', array(
                     'required' => true,
                     'value' => $this->controller->getAuthenticityToken(),
                     'validators' => array(array('custom', array($this->controller,'isValidToken'))),
                 )),
                 'school' => array('display', array(
                     'label' => $this->t('school'),
                     'content' => $app->school,
                     'element-wrapper' => 'div',
                 )),
                 'subject' => array('display', array(
                     'label' => $this->t('subject'),
                     'content' => $app->subject,
                     'element-wrapper' => 'div',
                 )),
                 'course' => array('display', array(
                     'label' => $this->t('course'),
                     'content' => $app->course,
                     'element-wrapper' => 'div',
                 )),
             );
        $files_url = DooUrlBuilder::url2('CourseController', 'files', array('id'=>$app->id), true);
        $elements['files'] = array('display', array(
             'label' => "<a target='_blank' href='$files_url'>{$this->t('file')}</a>",
             'content' => '',
             'element-wrapper' => 'div'
             ));
        $elements['submit'] = array('submit', array(
                 'label' => $this->t('submit'),
                 'attributes' => array('class' => 'buttons'),
                 'order' => 100,
             'field-wrapper' => 'div'
             ));
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements'=>$elements
        ));
        return $form;
    }

    public function getEditCourseForm($app) {
        if ($app && $app->id) {
            $action = DooUrlBuilder::url2('MyController', 'editApplication', array('id'=>$app->id), true);
        } else {
            $action = DooUrlBuilder::url2('MyController', 'apply', array('type'=>$app->type), true);
        }
        $elements = array(
                 'token' => array('hidden', array(
                     'required' => true,
                     'value' => $this->controller->getAuthenticityToken(),
                     'validators' => array(array('custom', array($this->controller,'isValidToken'))),
                 )),
                 'school' => array('text', array(
                     'label' => $this->t('school'),
                     'required' => false,
                     'value' => $app->school,
                     'attributes' => array('class' => 'control textbox'),
                     'element-wrapper' => 'div',
                 )),
                 'subject' => array('text', array(
                     'label' => $this->t('subject'),
                     'required' => false,
                     'value' => $app->subject,
                     'attributes' => array('class' => 'control textbox'),
                     'element-wrapper' => 'div',
                 )),
                 'course' => array('text', array(
                     'label' => $this->t('course'),
                     'required' => false,
                     'value' => $app->course,
                     'attributes' => array('class' => 'control textbox'),
                     'element-wrapper' => 'div',
                 )),
             );
        $i = 1;
        $elements['submit'] = array('submit', array(
                     'label' => $this->t('next'),
                     'attributes' => array('class' => 'buttons'),
                     'order' => 100,
                 'field-wrapper' => 'div'
                 ));
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements'=>$elements
        ));
        return $form;
    }
}
?>
