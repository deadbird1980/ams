<?php
require_once 'BaseController.php';

class FileController extends BaseController {

    protected $helper = 'FileHelper';

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

	public function index() {
        $this->renderAction('/file/index');
	}

	public function home() {
        $this->renderAction('/file/index');
	}

    public function json() {
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
        Doo::loadHelper('DooUrlBuilder');
        $url = DooUrlBuilder::url2('FileController', 'upload', null, true);
        $script_url = DooUrlBuilder::url2('FileController', 'view', null, true);
        $options = array('script_url'=>$script_url,
                       'upload_url'=>$script_url,
                       'delete_type'=>'POST',
                       'download_via_php'=>true);
        if (isset($_GET['application_id'])) {
            Doo::loadModel('Attachment');
            $attachment = new Attachment();
            $attachment->application_id = $_GET['application_id'];
            $options['upload_model'] = $attachment;
            $options['upload_dir'] = Doo::conf()->UPLOAD_PATH;
            $options['additional_elements'] = array('application_file');
        }
        Doo::loadClass('UploadHandler');
        $this->handler = new UploadHandler($options, false);
    }
}
?>
