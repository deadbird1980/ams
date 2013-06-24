<?php
class ApplicationHelper extends Helper {

    protected $dateElements = array('birthday', 'start_date', 'passport_start_date', 'passport_end_date', 'visa_start_date', 'visa_end_date');

    public function getApplicationForm($app) {
        if ($app->isVisa()) {
            $form = $this->getEuropeVisaForm($app);
        } else {
            $form = $this->getSchoolForm($app);
        }
        return $form;
    }

    public function getConfirmApplicationForm($app) {
        if ($app->isVisa()) {
            $form = $this->getConfirmVisaForm($app);
        } else {
            $form = $this->getConfirmSchoolForm($app);
        }
        return $form;
    }

    public function getConfirmVisaForm($app) {
        if (!$app->isSubmitted()) {
            //confirm page for student
            $action = DooUrlBuilder::url2('MyController', 'submitApplication', array('id'=>$app->id), true);
        } else {
            //confirm page for executor
            $action = DooUrlBuilder::url2('ApplicationController', 'confirm', array('id'=>$app->id), true);
        }

        $files_url = DooUrlBuilder::url2('MyController', 'uploadFiles', array('id'=>$app->id), true);
        Doo::loadModel('VisaApplication');
        $visaapp = new VisaApplication();
        $visaapp = $visaapp->getById_first($app->id);
        $elements =  array(
                 'token' => array('hidden', array(
                     'required' => true,
                     'value' => $this->controller->getAuthenticityToken(),
                     'validators' => array(array('custom', array($this->controller,'isValidToken'))),
                 )),
                 'type' => array('hidden', array(
                     'value' => $app->type,
                 )),
                 'start_date' => array('display', array(
                     'label' => $this->t('plan_start_date'),
                     'content' => $visaapp->start_date,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'passport' => array('display', array(
                     'content' => $this->t('passport_information'),
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
                     'label' => $this->t('passport_start_date'),
                     'content' => $visaapp->passport_start_date,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'passport_end_date' => array('display', array(
                     'label' => $this->t('passport_end_date'),
                     'content' => $visaapp->passport_end_date,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'visa' => array('display', array(
                     'content' => $this->t('visa_status'),
                 )),
                 'address' => array('display', array(
                     'label' => $this->t('uk_address'),
                     'content' => $visaapp->address,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'visa_start_date' => array('display', array(
                     'label' => $this->t('visa_start_date'),
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
                     'content' => $this->t('work_study_information'),
                 )),
                 'organization' => array('display', array(
                     'label' => $this->t('organization'),
                     'content' => $visaapp->organization,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )));
        $elements['files'] = array('display', array(
             'label' => "<a target='_blank' href='{$files_url}'>{$this->t('file')}</a>",
             'content' => '',
             'element-wrapper' => 'div'
             ));

        if (!$app->readonly) {
            if (!$app->AfterSubmitted()) {
                $elements['submit'] = array('submit', array(
                         'label' => $this->t('submit'),
                         'attributes' => array('class' => 'buttons'),
                         'order' => 100,
                     'field-wrapper' => 'div'
                     ));
            } else {
                if ($app->isSubmitted()) {
                    $elements = array_merge($elements, $this->getApproveElements());
                    $elements['submit'] = array('submit', array(
                             'label' => $this->t('confirm'),
                             'attributes' => array('class' => 'buttons'),
                             'order' => 100,
                         'field-wrapper' => 'div'
                         ));
                }
            }
        }
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements' => $elements
        ));
        return $form;
    }

    public function getConfirmSchoolForm($app) {
        if ($app->beforeSubmitted()) {
            $action = DooUrlBuilder::url2('MyController', 'submitApplication', array('id'=>$app->id), true);
        } else {
            $action = DooUrlBuilder::url2('ApplicationController', 'confirm', array('id'=>$app->id), true);
        }

        $visaapp = $app->createDetailApplication();
        $files_url = DooUrlBuilder::url2('MyController', 'uploadFiles', array('id'=>$app->id), true);
        $elements =  array(
                 'token' => array('hidden', array(
                     'required' => true,
                     'value' => $this->controller->getAuthenticityToken(),
                     'validators' => array(array('custom', array($this->controller,'isValidToken'))),
                 )),
                 'type' => array('hidden', array(
                     'value' => $app->type,
                 )),
                 'passport' => array('display', array(
                     'content' => '护照信息:',
                 )),
                 'passport_no' => array('display', array(
                     'label' => $this->t('passport_no'),
                     'content' => $visaapp->passport_no,
                 'element-wrapper' => 'div'
                 )),
                 'passport_name' => array('display', array(
                     'label' => $this->t('passport_name'),
                     'content' => $visaapp->passport_name,
                 'element-wrapper' => 'div'
                 )),
                 'birthday' => array('display', array(
                     'label' => $this->t('birthday'),
                     'content' => $visaapp->birthday,
                 'element-wrapper' => 'div'
                 )),
                 'passport_start_date' => array('display', array(
                     'label' => $this->t('passport_start_date'),
                     'content' => $visaapp->passport_start_date,
                 'element-wrapper' => 'div'
                 )),
                 'passport_end_date' => array('display', array(
                     'label' => $this->t('passport_end_date'),
                     'content' => $visaapp->passport_end_date,
                 'element-wrapper' => 'div'
                 )),
                 'visa' => array('display', array(
                     'content' => $this->t('visa_status'),
                 )),
                 'visa_start_date' => array('display', array(
                     'label' => $this->t('visa_start_date'),
                     'content' => $this->convertDateFromDB($visaapp->visa_start_date),
                 'element-wrapper' => 'div'
                 )),
                 'visa_end_date' => array('display', array(
                     'label' => $this->t('visa_end_date'),
                     'content' => $this->convertDateFromDB($visaapp->visa_end_date),
                 'element-wrapper' => 'div'
                 )),
                 'address' => array('display', array(
                     'label' => $this->t('cn_address'),
                     'content' => $visaapp->address,
                 'element-wrapper' => 'div'
                 )));
        $elements['files'] = array('display', array(
             'label' => "<a target='_blank' href='$files_url'>{$this->t('file')}</a>",
             'content' => '',
             'element-wrapper' => 'div'
             ));
        if (!$app->readonly) {
            if ($app->beforeSubmitted()) {
                $elements['submit'] = array('submit', array(
                         'label' => $this->t('submit'),
                         'attributes' => array('class' => 'buttons'),
                         'order' => 100,
                     'field-wrapper' => 'div'
                     ));
            } elseif ($app->isSubmitted()) {
                $elements = array_merge($elements, $this->getApproveElements());
                $elements['submit'] = array('submit', array(
                         'label' => $this->t('submit'),
                         'attributes' => array('class' => 'buttons'),
                         'order' => 100,
                     'field-wrapper' => 'div'
                     ));
            }
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
                 'token' => array('hidden', array(
                     'required' => true,
                     'value' => $this->controller->getAuthenticityToken(),
                     'validators' => array(array('custom', array($this->controller,'isValidToken'))),
                 )),
                 'type' => array('hidden', array(
                     'value' => $app->type,
                 )),
                 'start_date' => array('text', array(
                     'label' => $this->t('plan_start_date'),
                     'required' => true,
                     'value' => $this->convertDateFromDB($visaapp->start_date),
                     'validators' => array(array('date', $this->dateFormat)),
                     'attributes' => array('class' => $this->dateClass),
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
                     'value' => $this->convertDateFromDB($visaapp->birthday),
                     'validators' => array(array('date', $this->dateFormat)),
                     'attributes' => array('class' => $this->dateClass),
                 'element-wrapper' => 'div'
                 )),
                 'passport_start_date' => array('text', array(
                     'label' => $this->t('passport_start_date'),
                     'required' => true,
                     'value' => $this->convertDateFromDB($visaapp->passport_start_date),
                     'validators' => array(array('date', $this->dateFormat)),
                     'attributes' => array('class' => $this->dateClass),
                 'element-wrapper' => 'div'
                 )),
                 'passport_end_date' => array('text', array(
                     'label' => $this->t('end_date'),
                     'required' => true,
                     'value' => $this->convertDateFromDB($visaapp->passport_end_date),
                     'validators' => array(array('date', $this->dateFormat)),
                     'attributes' => array('class' => $this->dateClass),
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
                     'label' => $this->t('visa_start_date'),
                     'required' => true,
                     'value' => $this->convertDateFromDB($visaapp->visa_start_date),
                     'validators' => array(array('date', $this->dateFormat)),
                     'attributes' => array('class' => $this->dateClass),
                 'element-wrapper' => 'div'
                 )),
                 'visa_end_date' => array('text', array(
                     'label' => $this->t('visa_end_date'),
                     'required' => true,
                     'value' => $this->convertDateFromDB($visaapp->visa_end_date),
                     'validators' => array(array('date', $this->dateFormat)),
                     'attributes' => array('class' => $this->dateClass),
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
        $school_app = $app->createDetailApplication();
        $elements = array(
                 'token' => array('hidden', array(
                     'required' => true,
                     'value' => $this->controller->getAuthenticityToken(),
                     'validators' => array(array('custom', array($this->controller,'isValidToken'))),
                 )),
                 'type' => array('hidden', array(
                     'value' => $app->type,
                 )),
                 'passport' => array('display', array(
                     'content' => $this->t('passport_information'),
                 )),
                 'passport_no' => array('text', array(
                     'label' => $this->t('passport_no'),
                     'required' => true,
                     'value' => $school_app->passport_no,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'passport_name' => array('text', array(
                     'label' => $this->t('passport_name'),
                     'required' => true,
                     'value' => $school_app->passport_name,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 )),
                 'birthday' => array('text', array(
                     'label' => $this->t('birthday'),
                     'required' => true,
                     'value' => $this->convertDateFromDB($school_app->birthday),
                     'attributes' => array('class' => $this->dateClass),
                 'element-wrapper' => 'div'
                 )),
                 'passport_start_date' => array('text', array(
                     'label' => $this->t('passport_start_date'),
                     'required' => true,
                     'value' => $this->convertDateFromDB($school_app->passport_start_date),
                     'attributes' => array('class' => $this->dateClass),
                 'element-wrapper' => 'div'
                 )),
                 'passport_end_date' => array('text', array(
                     'label' => $this->t('passport_end_date'),
                     'required' => true,
                     'value' => $this->convertDateFromDB($school_app->passport_end_date),
                     'attributes' => array('class' => $this->dateClass),
                 'element-wrapper' => 'div'
                 )),
                 'visa_start_date' => array('text', array(
                     'label' => $this->t('visa_start_date'),
                     'required' => true,
                     'value' => $this->convertDateFromDB($school_app->visa_start_date),
                     'attributes' => array('class' => $this->dateClass),
                 'element-wrapper' => 'div'
                 )),
                 'visa_end_date' => array('text', array(
                     'label' => $this->t('visa_end_date'),
                     'required' => true,
                     'value' => $this->convertDateFromDB($school_app->visa_end_date),
                     'attributes' => array('class' => $this->dateClass),
                 'element-wrapper' => 'div'
                 )),
                 'address' => array('text', array(
                     'label' => $this->t('cn_address'),
                     'required' => true,
                     'value' => $school_app->address,
                     'attributes' => array('class' => 'control textbox validate[required]'),
                 'element-wrapper' => 'div'
                 ))
             );
        if ($app->isRejected()) {
            $elements['comment'] = array('display', array(
                 'label' => $this->t('comment'),
                 'content' => $app->comment,
                 'element-wrapper' => 'div'
                 ));
        }

        if ($this->controller->isAdmin()) {
            $apps = $school_app->CourseApplication;
            $i = 1;
            foreach($apps as $app) {
                $elements['school'.$i] = array('display', array(
                             'label' => $this->t('school'). ' ' . $i,
                             'content' => "{$app->school}  {$app->subject}  {$app->course}",
                         'element-wrapper' => 'div'
                         ));
                $i++;
            }
        } else {
            $apps = $school_app->CourseApplication;
            $i = 1;
            foreach($apps as $app) {
                $elements['school'.$i] = array('display', array(
                             'label' => $this->t('school'). ' ' . $i,
                             'content' => "{$app->school}  {$app->subject}  {$app->course}",
                         'element-wrapper' => 'div'
                         ));
                $i++;
            }
        }

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

    public function getNewApplicationForm() {
        Doo::loadHelper('DooForm');
        Doo::loadHelper('DooUrlBuilder');
        $action = DooUrlBuilder::url2('ApplicationController', 'create', array('user_id'=>$this->controller->params['user_id']), true);
        //<optgroup label="----------"></optgroup>
        $options = $this->getApplicationTypeDropDown();
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
                 'type' => array('select', array(
                     'label' => $this->t('type'),
                     'required' => true,
                     'value' => array(''),
                     'multioptions' => $options,
                     'attributes' => array('class' => 'control textbox validate[required,not_empty]'),
                 'element-wrapper' => 'div'
                 )),
                 's1'=> array('display', array(
                     'content' => '&nbsp;&nbsp',
                     'attributes' => array('class' => 'hidden'),
                 )),
                 'add_link'=> array('display', array(
                     'content' => '&nbsp;&nbsp<a id="add-school" href="#">'.$this->t('add_school').'</a>',
                     'attributes' => array('class' => 'hidden'),
                 )),
                 'school' => array('text', array(
                     'label' => $this->t('school'),
                     'required' => false,
                     'value' => '',
                     'attributes' => array('name'=>'schools[]', 'class' => 'control textbox hidden validate[required]'),
                     'element-wrapper' => 'div',
                 )),
                 'subject' => array('text', array(
                     'label' => $this->t('subject'),
                     'required' => false,
                     'value' => '',
                     'attributes' => array('name'=>'subjects[]', 'class' => 'control textbox hidden'),
                     'element-wrapper' => 'div',
                 )),
                 'course' => array('text', array(
                     'label' => $this->t('course'),
                     'required' => false,
                     'value' => '',
                     'attributes' => array('name'=>'courses[]', 'class' => 'control textbox hidden validate[required]'),
                     'element-wrapper' => 'div',
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

    public function getApplicationTypeForm($app) {
        Doo::loadHelper('DooForm');
        Doo::loadHelper('DooUrlBuilder');
        $action = DooUrlBuilder::url2('ApplicationController', 'editType', array('id'=>$app->id), true);
        $options = $this->getApplicationTypeDropDown($app->isSchool()?'school':'visa');
        $school_app = $app->createDetailApplication();
        $elements = array(
                 'type' => array('select', array(
                     'label' => $this->t('type'),
                     'required' => true,
                     'value' => $app->type,
                     'multioptions' => $options,
                     'attributes' => array('class' => 'control textbox validate[required,not_empty]'),
                 'element-wrapper' => 'div'
                 )));
        if ($app->isSchool()) {
            $apps = $school_app->CourseApplication;
            $i = 0;
            foreach($apps as $course) {
                $i++;
                $elements['id'.$i] = array('hidden', array(
                     'required' => true,
                     'value' => $course->id,
                     'attributes' => array('name'=>'course_ids[]'),
                     'validators' => array(array('custom', array($this->controller,'isValidToken'))),
                ));
                $elements['school'.$i] = array('text', array(
                         'label' => $this->t('school'),
                         'required' => false,
                         'value' => $course->school,
                         'attributes' => array('name'=>'schools[]', 'class' => 'control textbox'),
                         'element-wrapper' => 'div',
                     ));
                $elements['subject'.$i] = array('text', array(
                         'label' => $this->t('subject'),
                         'required' => false,
                         'value' => $course->subject,
                         'attributes' => array('name'=>'subjects[]', 'class' => 'control textbox'),
                         'element-wrapper' => 'div',
                     ));
                $elements['course'.$i] = array('text', array(
                         'label' => $this->t('course'),
                         'required' => false,
                         'value' => $course->course,
                         'attributes' => array('class' => 'control textbox'),
                         'element-wrapper' => 'div',
                     ));

            }
            $elements['s1'] = array('display', array(
                     'content' => '&nbsp;&nbsp',
                     'attributes' => array('class' => 'hidden'),
                 ));
            $elements['add_link'] = array('display', array(
                     'content' => '&nbsp;&nbsp<a id="add-school" href="#">'.$this->t('add_school').'</a>',
                     'attributes' => array('class' => 'hidden'),
                 ));
        }
        $elements['submit'] = array('submit', array(
                     'label' => $this->t('update'),
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
        $form->addDisplayGroup('group', array('school','course', 'subject'));
        return $form;
    }

    public function getApplicationSearchForm() {
        Doo::loadHelper('DooForm');
        Doo::loadHelper('DooUrlBuilder');
        $action = DooUrlBuilder::url2('ApplicationController', 'index', array(), true);
        $elements = array(
                 'command' => array('hidden', array(
                     'value' => 'search',
                 )),
                 'type' => array('select', array(
                     'label' => $this->t('type'),
                     'required' => true,
                     'value' => '',
                     'multioptions' => $this->getApplicationTypeDropDown(),
                     'attributes' => array('class' => 'control textbox validate[required,not_empty]'),
                 'element-wrapper' => 'div'
                 )));
        $elements['submit'] = array('submit', array(
                     'label' => $this->t('search'),
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
        $form->addDisplayGroup('group', array('school','course', 'subject'));
        return $form;
    }

    public function getApplicationStatusForm($app) {
        Doo::loadHelper('DooForm');
        Doo::loadHelper('DooUrlBuilder');
        $action = DooUrlBuilder::url2('ApplicationController', 'status', array('id'=>$app->id), true);
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
                 'type' => array('hidden', array(
                     'value' => $app->type,
                 )),
                 'status' => array('select', array(
                     'required' => true,
                     'multioptions' => array(Application::CREATED=>$this->t(Application::CREATED),
                                             Application::IN_PROGRESS=>$this->t(Application::IN_PROGRESS),
                                             Application::SUBMITTED=>$this->t(Application::SUBMITTED),
                                             Application::CONFIRMED=>$this->t(Application::CONFIRMED),
                                             Application::SENT=>$this->t(Application::SENT),
                                             Application::REPLIED=>$this->t(Application::REPLIED),
                                             Application::DONE=>$this->t(Application::DONE),),
                     'label' => $this->t('status'),
                     'value' => $app->status,
                     'attributes' => array('class' => 'control type validate[required]'),
                     'element-wrapper' => 'div'
                 )),
                 'paid' => array('select', array(
                     'required' => true,
                     'multioptions' => array('1'=>$this->t('yes'), '0'=>$this->t('no')),
                     'label' => $this->t('paid'),
                     'value' => $app->paid,
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

    private function getApproveElements() {
        $elements = array();
        $elements['action'] = array('MultiRadio', array(
             'multioptions' => array(1 => $this->t('confirm'), 2 => $this->t('deny')),
             'label' => $this->t('action'),
             'attributes' => array('class' => 'control radio'),
             'element-wrapper' => 'div',
             ));
        $elements['comment'] = array('textarea', array(
             'attributes' => array('class' => 'control textarea'),
             'element-wrapper' => 'div',
             ));
        $elements['submit'] = array('submit', array(
                 'label' => $this->t('submit'),
                 'attributes' => array('class' => 'buttons'),
                 'order' => 100,
             'field-wrapper' => 'div'
             ));
        return $elements;
    }

    private function getApplicationTypeDropDown($type='') {
        // type = school/visa
        $options = array( ''=>'' );
        if ($type == '' || $type == 'visa') {
            $options['----'.$this->t('visa').'----'] = array(
                         'visa_europe' => $this->t('visa_europe'),
                         'visa_t1' => $this->t('visa_t1'),
                         'visa_t2' => $this->t('visa_t2'),
                         'visa_t4' => $this->t('visa_t4'),
                         'visa_other' => $this->t('visa_other'));
        }
        if ($type == '' || $type == 'school') {
             $options['----'.$this->t('school').'----'] = array('language' => $this->t('language'),
                         'gcse' => $this->t('gcse'),
                         'a-level' => $this->t('a-level'),
                         'pre-bachelor' => $this->t('pre-bachelor'),
                         'bachelor' => $this->t('bachelor'),
                         'pre-master' => $this->t('pre-master'),
                         'master' => $this->t('master'),
                         'doctor' => $this->t('doctor'));
        }
        return $options;
    }
}
?>
