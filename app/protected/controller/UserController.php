<?php
require_once 'BaseController.php';

class UserController extends BaseController {
    protected $helper = 'UserHelper';

	public function beforeRun($resource, $action){
        if ($rtn = parent::beforeRun($resource, $action)) {
            return $rtn;
        }
        $this->setUser();
        $this->sortField = 'id';
        $this->orderType = 'desc';
        if (isset($this->params['sortField'])) {
          $this->data['sortField'] = $this->sortField = $this->params['sortField'];
        }
        if (isset($this->params['orderType'])) {
          $this->orderType = $this->params['orderType'];
        }
    }

	public function index() {
        Doo::loadHelper('DooPager');
        $user = $this->auth->user;
        $u = new User;
        $scope = $user->scopeSeenByMe();
        // operations
        if ($this->isPost() && isset($_POST['operation'])) {
            if ($_POST['operation'] == 'delete') {
                foreach($_POST['users'] as $id) {
                    $u = $user->getById_first($id);
                    $u->destroy();
                }
                $this->data['message'] = $this->t('item_deleted');
            } else if ($_POST['operation'] == 'export') {
                foreach($_POST['users'] as $id) {
                    $u = $user->getById_first($id);
                    $u->export();
                }
            }
        }
        if (($user_count = $u->count($scope)) > 0) {
        //if default, no sorting defined by user, show this as pager link
            $page_size = $this->getPageSize();
            $pages = $this->getPages();
            if($this->sortField=='email' && $this->orderType=='desc'){
                $pager = new DooPager(Doo::conf()->APP_URL.$this->getRange().'/users/page', $user_count, $page_size, $pages);
            }else{
                $pager = new DooPager(Doo::conf()->APP_URL.$this->getRange()."/users/sort/{$this->sortField}/{$this->orderType}/page", $user_count, $page_size, $pages);
            }

            if(isset($this->params['pindex']))
                $pager->paginate(intval($this->params['pindex']));
            else
                $pager->paginate(1);

            $this->data['pager'] = $pager->output;

            $columns = 'id,email,first_name,last_name,first_name_alphabet,last_name_alphabet,phone,qq,status,confirm_code';
            //Order by ASC or DESC
            if($this->orderType=='desc'){
                $this->data['users'] = $u->limit($pager->limit, null, $this->sortField,
                                            array_merge(array('select'=>$columns), $scope)
                                      );
                $this->data['orderType'] = 'asc';
            }else{
                $this->data['users'] = $u->limit($pager->limit, $this->sortField, null,
                                            //we don't want to select the Content (waste of resources)
                                            array_merge(array('select'=>$columns), $scope)
                                      );
                $this->data['orderType'] = 'desc';
            }
        }
        $form = $this->helper->getActivateUserForm();
        $this->data['form'] = $form->render();
        if ($this->auth->user->isAdmin()) {
            $this->renderAction('/admin/user/index');
        } else {
            $this->renderAction('/my/user/index');
        }
	}

	public function save() {
		$this->data['title'] = 'new User';
		$this->data['message'] = '';
        $this->data['user'] = new User();
        $form = $this->helper->getUserForm($this->data['user']);
        if ($form->isValid($_POST)) {
            $u = $this->data['user'];
            $u->first_name = $_POST['first_name'];
            $u->last_name = $_POST['last_name'];
            $u->first_name_alphabet = $_POST['first_name_alphabet'];
            $u->last_name_alphabet = $_POST['last_name_alphabet'];
            $u->phone = $_POST['phone'];
            $u->email = $_POST['email'];
            $u->password = $_POST['password'];
            $u->qq = $_POST['qq'];
            $u->confirm_code = $_POST['confirm_code'];
            $u->type = $_POST['type'];
            $u->insert();
            $form = $this->helper->getUserForm($u);
        }
        $this->data['form'] = $form->render();
		$this->renderAction('/admin/user/edit');
	}

	public function create() {
		$this->data['title'] = 'new User';
		$this->data['message'] = '';
        $this->data['user'] = new User;
        $form = $this->helper->getUserForm($this->data['user']);
        $this->data['form'] = $form->render();
		$this->renderAction('/admin/user/edit');
	}

	public function edit() {
		$this->data['title'] = 'User';
        $form = $this->helper->getUserForm($this->data['user']);
        if ($this->isPost() && $form->isValid($_POST)) {
            $u = $this->data['user'];
            $u->first_name = $_POST['first_name'];
            $u->last_name = $_POST['last_name'];
            $u->first_name_alphabet = $_POST['first_name_alphabet'];
            $u->last_name_alphabet = $_POST['last_name_alphabet'];
            $u->email = $_POST['email'];
            $u->password = $_POST['password'];
            $u->qq = $_POST['qq'];
            $u->confirm_code = $_POST['confirm_code'];
            if ($this->auth->user->isAdmin() && isset($_POST['type'])) {
                $u->type = $_POST['type'];
            }
            $u->status = $_POST['status'];
            $u->update(array('where'=>"id={$u->id}"));
            $this->data['message'] = $this->t('updated');
            $form = $this->helper->getUserForm($u);
        }
        $this->data['form'] = $form->render();
		$this->renderAction('/'.$this->getRange().'/user/edit');
	}

    public function activate() {
        $form = $this->helper->getActivateUserForm();
        if ($this->isPost() && $form->isValid($_POST)) {
            $u = new User();
            $u->confirm_code = $_POST['confirm_code'];
            $u = $this->db()->find($u, array('limit'=>1));
            if (!$u->isRegistered()) {
                $this->data['message'] = $this->t('already_activated');
            } else {
                $u->activate($this->auth->user);
                $this->data['user'] = $this->auth->user;
                $this->data['activated_user'] = $u;
                $this->notifyUser($this->auth->user, $this->t('account_activated'), 'activated');
                $this->data['message'] = $this->t('user_activated');
            }
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/my/user/activate');
    }

    private function setUser() {
        Doo::loadModel('User');
        if (isset($this->params['id'])) {
            $u = new User;
            if (is_numeric($this->params['id'])) {
                $u->id = $this->params['id'];
            } else {
                $u->confirm_code = $this->params['id'];
            }
            $user = $this->db()->find($u, array('limit'=>1));
            $this->data['user'] = $user;
        }
    }


}
?>
