<?php
require_once 'BaseController.php';

class EmailController extends BaseController {
	public function index() {
        Doo::loadHelper('DooPager');
        Doo::loadModel('Email');
        $email = new Email();
        $options = $email->scopeSeenByUser($this->user);
        if(isset($this->params['user_id'])) {
            if ($u = $this->user->getById_first($this->params['user_id'])) {
                if ($u->isAvailabeTo($this->user)) {
                    $options['where'] = "{$options['where']} and user_id={$u->id}";
                } else {
                    return array('not available', 404);
                }
            } else {
                //not available to current user
                return array('not available', 404);
            }
            $this->data['user_id'] = $this->params['user_id'];
        } else {
            $u = $this->user;
        }
        // operations
        if ($this->isPost() && isset($_POST['operation'])) {
            if ($_POST['operation'] == 'delete') {
                foreach($_POST['applications'] as $email_id) {
                    $email2delete = $email->getById_first($email_id);
                    $email2delete->delete();
                }
                $this->data['message'] = $this->t('item_deleted');
            } else if ($_POST['operation'] == 'paid') {
                foreach($_POST['applications'] as $email_id) {
                    $email2pay = $email->getById_first($email_id);
                    $email2pay->paid();
                }
                $this->data['message'] = $this->t('item_deleted');
            } else if ($_POST['operation'] == 'export') {
                foreach($_POST['applications'] as $email_id) {
                    $email2export = $email->getById_first($email_id);
                    $email2export->export();
                }
                $this->data['message'] = $this->t('item_deleted');
            }
        }

        if (($count = $email->count()) > 0) {
            if (isset($this->params['sortField'])) {
                $this->sortField = $this->params['sortField'];
            }
            if (isset($this->params['orderType'])) {
                $this->orderType = $this->params['orderType'];
            }
            $row_perpage = Doo::conf()->ROWS_PERPAGE;
            $pages = Doo::conf()->PAGES;
            //if default, no sorting defined by user, show this as pager link
            if($this->sortField=='Application.id' && $this->orderType=='desc'){
                $pager = new DooPager(Doo::conf()->APP_URL.$this->data['range'].'/applications/page', $count, $row_perpage, $pages);
            }else{
                $pager = new DooPager(Doo::conf()->APP_URL.$this->data['range']."/applications/sort/{$this->sortField}/{$this->orderType}/page", $count, $row_perpage, $pages);
            }

            if(isset($this->params['pindex']))
                $pager->paginate(intval($this->params['pindex']));
            else
                $pager->paginate(1);

            $this->data['pager'] = $pager->output;

            $options['limit'] = $pager->limit;

            //Order by ASC or DESC
            if($this->orderType=='asc'){
                $options['asc'] = $this->sortField;
                $this->data['order'] = 'asc';
                $this->data['orderType'] = 'desc';
            }else{
                $options['desc'] = $this->sortField;
                $this->data['order'] = 'desc';
                $this->data['orderType'] = 'asc';
            }
            if ($this->user->isAdmin()) {
                $this->data['applications'] = $email->relateMany(array('User','Assignee'),array('User'=>$options));
            } else {
                $this->data['applications'] = $email->relateUser($options);
            }
            $this->data['sortField'] = $this->sortField;
        }

        if ($this->user->isAdmin()) {
            $this->renderAction('/admin/email/index');
        } else {
            $this->renderAction('/my/email/index');
        }
	}

}
?>
