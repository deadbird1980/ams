<?php
require_once 'BaseController.php';

class MyController extends BaseController {

    //Default sort by createtime field
    public $sortField = 'username';
    public $orderType = 'desc';
    public static $tags;

	public function beforeRun($resource, $action){
        parent::beforeRun($resource, $action);
		session_start();

		//if not login, group = anonymous
		$role = (isset($_SESSION['user']['group'])) ? $_SESSION['user']['group'] : 'anonymous';

		if($role!='anonymous'){
				$role = 'student';
		}

		//check against the ACL rules
		if($rs = $this->acl()->process($role, $resource, $action )){
			//echo $role .' is not allowed for '. $resource . ' '. $action;
			return $rs;
		}
	}

    /**
     * Display the list of paginated Posts (draft and published)
     */
	function home() {
        Doo::loadHelper('DooPager');
        Doo::loadModel('User');

        $u = new User();
        //if default, no sorting defined by user, show this as pager link
        if($this->sortField=='username' && $this->orderType=='desc'){
            $pager = new DooPager(Doo::conf()->APP_URL.'admin/user/page', $u->count(), 6, 10);
        }else{
            $pager = new DooPager(Doo::conf()->APP_URL."admin/user/sort/$this->sortField/$this->orderType/page", $u->count(), 6, 10);
        }

        if(isset($this->params['pindex']))
            $pager->paginate(intval($this->params['pindex']));
        else
            $pager->paginate(1);

        $this->data['pager'] = $pager->output;

        //Order by ASC or DESC
        if($this->orderType=='desc'){
            $this->data['users'] = $u->limit($pager->limit, null, $this->sortField,
                                        //we don't want to select the Content (waste of resources)
                                        array('select'=>'id,username,email,first_name,last_name')
                                  );
            $this->data['order'] = 'asc';
        }else{
            $this->data['users'] = $u->limit($pager->limit, $this->sortField, null,
                                        //we don't want to select the Content (waste of resources)
                                        array('select'=>'id,username,email,first_name,last_name')
                                  );
            $this->data['order'] = 'desc';
        }

        $this->renderAction('admin');
	}

}
?>
