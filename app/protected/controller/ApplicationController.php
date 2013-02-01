<?php
require_once 'BaseController.php';

class ApplicationController extends BaseController {

    protected $user;
    protected $sortField;
    protected $orderType;

	public function index() {
        Doo::loadHelper('DooPager');
        Doo::loadModel('Application');
        if(isset($this->params['user_id'])) {
            $scope = $this->user->seenByMe();
            if ($u = $this->user->getById_first($this->params['user_id'])) {
                if ($u->isAvailabeTo($this->user)) {
                } else {
                    return array('not available', 404);
                }
            } else {
                //not available to current user
                return array('not available', 404);
            }
        } else {
            $u = $this->user;
        }
        $app = new Application();
        //if default, no sorting defined by user, show this as pager link
        if($this->sortField=='email' && $this->orderType=='desc'){
            $pager = new DooPager(Doo::conf()->APP_URL.'admin/user/page', $app->count($app->scopeSeenByUser($u)), 6, 10);
        }else{
            $pager = new DooPager(Doo::conf()->APP_URL."admin/user/sort/$this->sortField/$this->orderType/page", $app->count($app->seenByUser($u)), 6, 10);
        }

        if(isset($this->params['pindex']))
            $pager->paginate(intval($this->params['pindex']));
        else
            $pager->paginate(1);

        $this->data['pager'] = $pager->output;

        $columns = 'id,type,start_date,status,assignee_id,user_id';
        //Order by ASC or DESC
        if($this->orderType=='desc'){
            $this->data['applications'] = $app->limit($pager->limit, null, $this->sortField,
                                        array_merge(array('select'=>$columns), $app->scopeSeenByMe($u))
                                  );
            $this->data['order'] = 'asc';
        }else{
            $this->data['applications'] = $app->limit($pager->limit, $this->sortField, null,
                                        //we don't want to select the Content (waste of resources)
                                        array_merge(array('select'=>$columns), $app->scopeSeenByUser($u))
                                  );
            $this->data['order'] = 'desc';
        }
        $this->renderAction('/my/application/index');
	}

    public function editApplication() {
        Doo::loadModel('Application');

        $app = new Application();
        $this->data['application'] = $app->getById_first($this->params['id']);

        $form = $this->getEuropeVisaForm($app);
        if ($this->isPost() && $form->isValid($_POST)) {
            $id = $this->params['id'];
            $app = new Application();
            $app = $app->getById_first($id);
            $visaapp = new VisaApplication();
            $visaapp = $visaapp->getById_first($id);
            $app = new VisaApplication($_POST);
            $app->id = $id;
            $app->update_attributes($_POST, array('where'=>"id=${id}"));
            Doo::loadHelper('DooUrlBuilder');
            return DooUrlBuilder::url2('MyController', 'uploadFiles', array('id'=>$id), true);
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/application/edit');
    }

    public function apply() {
        Doo::loadModel('Application');
        $app = new Application();
        $app->type = $this->params['type'];
        $this->data['application'] = $app;
        $form = $this->getEuropeVisaForm();
        if ($this->isPost() && $form->isValid($_POST)) {
            Doo::loadModel('Application');
            $a = new Application();
            $a->user_id = $this->user->id;
            if ($this->user->activated_by) {
                $a->assignee_id = $this->user->activated_by;
            }
            $this->data['message'] = "User activated!";
            $a->type = 'visa';
            $a->status = 'in_progress';
            $id = $a->insert();
            // application detail
            if ($this->params['type'] == 'visa') {
                Doo::loadModel('VisaApplication');
                $a = new VisaApplication($_POST);
                $a->id = $id;
                $a->insert();
            }
            return Doo::conf()->APP_URL . "index.php/my/applications/{$id}/files";
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/application/edit');
    }

    public function uploadFiles() {
        Doo::loadModel('Application');
        $app = new Application();
        $this->data['application'] = $app->getById_first($this->params['id']);

        $form = $this->getFilesForm();
        if ($this->isPost() && $form->isValid($_POST)) {
            $u = new User();
            $u->findByConfirm_code($_POST['confirm_code']);
            $u->activate($this->user);
            $this->data['message'] = "User activated!";
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/application/files');
    }

    public function confirmApplication() {
        Doo::loadModel('Application');

        $app = new Application();
        $this->data['application'] = $app->getById_first($this->params['id']);

        $form = $this->getConfirmApplicationForm();
        if ($this->isPost() && $form->isValid($_POST)) {
            $id = $this->params['id'];
            $app = new Application();
            $app = $app->getById_first($id);
            $visaapp = new VisaApplication();
            $visaapp = $visaapp->getById_first($id);
            $app = new VisaApplication($_POST);
            $app->id = $id;
            $app->update_attributes($_POST, array('where'=>"id=${id}"));
            return Doo::conf()->APP_URL . "index.php/my/applications/{$id}/files";
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/application/edit');
    }

    private function getFilesForm() {
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

    private function getEuropeVisaForm() {
        Doo::loadHelper('DooForm');
        Doo::loadHelper('DooUrlBuilder');
        $app = $this->data['application'];
        if ($app && $app->id) {
            $action = DooUrlBuilder::url2('MyController', 'editApplication', array('id'=>$app->id), true);
        } else {
            $action = DooUrlBuilder::url2('MyController', 'apply', array('type'=>$app->type), true);
        }
        Doo::loadModel('VisaApplication');
        $visaapp = new VisaApplication();
        $visaapp = $visaapp->getById_first($app->id);
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

    private function getConfirmApplicationForm() {
        Doo::loadHelper('DooForm');
        $app = $this->data['application'];
        if ($app) {
            if ($app->id) {
            $action = Doo::conf()->APP_URL . 'index.php/my/applications/'. $app->id;
            } else {
            $action = Doo::conf()->APP_URL . 'index.php/my/applications/create/'.$app->type;
            }
        } else {
            $action = Doo::conf()->APP_URL . 'index.php/my/applications/create/'. $this->params['type'];
        }
        Doo::loadModel('VisaApplication');
        $visaapp = new VisaApplication();
        $visaapp = $visaapp->getById_first($app->id);
        $form = new DooForm(array(
             'method' => 'post',
             'action' => $action,
             'attributes'=> array('id'=>'form', 'name'=>'form', 'class'=>'Zebra_Form'),
             'elements' => array(
                 'type' => array('hidden', array(
                     'value' => $app->type,
                 )),
                 'start_date' => array('display', array(
                     'label' => 'Start Date:',
                     'content' => $visaapp->start_date,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'passport' => array('display', array(
                     'content' => '护照信息:',
                 )),
                 'passport_no' => array('display', array(
                     'label' => '护照号码:',
                     'content' => $visaapp->passport_no,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'passport_name' => array('display', array(
                     'label' => '护照姓名:',
                     'content' => $visaapp->passport_name,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'birthday' => array('display', array(
                     'label' => '生日:',
                     'content' => $visaapp->birthday,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'passport_start_date' => array('display', array(
                     'label' => '护照生效期:',
                     'content' => $visaapp->passport_start_date,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'passport_end_date' => array('display', array(
                     'label' => '护照截止日期:',
                     'content' => $visaapp->passport_end_date,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'visa' => array('display', array(
                     'content' => '当前签证状态:',
                 )),
                 'address' => array('display', array(
                     'label' => '英国地址:',
                     'content' => $visaapp->address,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'visa_start_date' => array('display', array(
                     'label' => '签证开始日期:',
                     'content' => $visaapp->visa_start_date,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'visa_end_date' => array('display', array(
                     'label' => '签证结束日期:',
                     'content' => $visaapp->visa_end_date,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'work' => array('display', array(
                     'content' => '目前工作学习状况:',
                 )),
                 'organization' => array('display', array(
                     'label' => '公司/学校名称:',
                     'content' => $visaapp->organization,
                     'attributes' => array('class' => 'control'),
                 'element-wrapper' => 'div'
                 )),
                 'files' => array('display', array(
                     'content' => '文件:',
                 )),
                 'submit' => array('submit', array(
                     'label' => "提交",
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
