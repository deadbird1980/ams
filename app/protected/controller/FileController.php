<?php
require_once 'BaseController.php';

class FileController extends BaseController {

    protected $user;
	public function beforeRun($resource, $action){
        parent::beforeRun($resource, $action);

		//if not login, group = anonymous
		$role = (isset($this->session->user['type'])) ? $this->session->user['type'] : 'anonymous';

		if($role!='anonymous'){
				$role = 'student';
		}

		//check against the ACL rules
		if($rs = $this->acl()->process($role, $resource, $action )){
			//echo $role .' is not allowed for '. $resource . ' '. $action;
			//return $rs;
		}

        Doo::loadModel('User');

        $u = new User();
        $u->id = $this->session->user['id'];
        $this->user = $this->db()->find($u, array('limit'=>1));
        $this->setHandler();
	}

	public function home() {
        $this->renderAction('/file/index');
	}

    public function view() {
        $this->handler->get(true);
    }

    public function upload() {
        $this->handler->post(true);
    }

    public function create() {
        $form = $this->getFilesForm();
        if ($this->isPost() && $form->isValid($_POST)) {
            $u = new User();
            $u->findByConfirm_code($_POST['confirm_code']);
            $u->activate($this->user);
            $this->data['message'] = "User activated!";
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/application/file');
    }

    protected function setHandler() {
        Doo::loadClass('UploadHandler');
        Doo::loadModel('Attachment');
        $attachment = new Attachment();
        if (isset($_GET['application_id'])) {
            $attachment->application_id = $_GET['application_id'];
        }
        Doo::loadHelper('DooUrlBuilder');
        $url = DooUrlBuilder::url2('FileController', 'upload', null, true);
        $script_url = DooUrlBuilder::url2('FileController', 'view', null, true);
        $this->handler = new UploadHandler(array('script_url'=>$script_url,
                                           'upload_url'=>$script_url,
                                           'upload_model'=>$attachment,
                                           'download_via_php'=>true), false);
    }
}
?>
