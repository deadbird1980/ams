<?php
require_once 'BaseController.php';

class AdminController extends BaseController {

    //Default sort by createtime field
    public $sortField = 'email';
    public $orderType = 'desc';
    public static $tags;
    protected $helper = 'ApplicationHelper';

    /**
     * Display the list of paginated Posts (draft and published)
     */
	function home() {
        Doo::loadHelper('DooPager');
        Doo::loadModel('User');

        $u = new User();
        //if default, no sorting defined by user, show this as pager link
        $page_size = $this->getPageSize();
        $pages = $this->getPages();
        if($this->sortField=='email' && $this->orderType=='desc'){
            $pager = new DooPager(Doo::conf()->APP_URL.'admin/users/page', $u->count(), $page_size, $pages);
        }else{
            $pager = new DooPager(Doo::conf()->APP_URL."admin/users/sort/$this->sortField/$this->orderType/page", $u->count(), $page_size, $pages);
        }

        if(isset($this->params['pindex']))
            $pager->paginate(intval($this->params['pindex']));
        else
            $pager->paginate(1);

        $this->data['pager'] = $pager->output;

        //Order by ASC or DESC
        if($this->orderType=='desc'){
            $this->data['users'] = $u->limit($pager->limit, null, $this->sortField);
            $this->data['orderType'] = 'asc';
        }else{
            $this->data['users'] = $u->limit($pager->limit, $this->sortField, null,
                                        //we don't want to select the Content (waste of resources)
                                        array('select'=>$columns)
                                  );
            $this->data['orderType'] = 'desc';
        }

        $this->renderAction('admin/index');
	}

    private function setApplication() {
        Doo::loadModel('Application');

        $app = new Application();
        $app = $this->data['application'] = $app->getById_first($this->params['id']);
        if ($app && !$app->canBeSeen($this->user)) {
            $app = null;
        }
        return $app;
    }

    public function editApplication() {
        if (!($app = $this->setApplication())) {
            return array('no access', 404);
        }
        if ($this->data['application']->afterSubmitted()) {
            $form = $this->helper->getConfirmApplicationForm($app);
            $this->data['form'] = $form->render();
            $this->renderAction('/admin/application/view');
        } else {
            $form = $this->helper->getApplicationForm($app);
            if ($this->isPost() && $form->isValid($_POST)) {
                $id = $this->params['id'];
                $app = new Application();
                $app = $app->getById_first($id);
                $app_detail = $app->createDetailApplication();
                $app_detail->update_attributes($_POST, array('where'=>"id=${id}"));
                Doo::loadHelper('DooUrlBuilder');
                return DooUrlBuilder::url2('AdminController', 'uploadFiles', array('id'=>$id), true);
            }
            $this->data['form'] = $form->render();
            $this->renderAction('/admin/application/edit');
        }
    }
    /**
     * Show single blog post for editing
     */
	function getUser() {
        Doo::loadModel('User');
        $p = new Post();
        $p->id = intval($this->params['pid']);
        
        try{
            $data['post'] = $p->relateTag(
                                        array(
                                            'limit'=>'first',
                                            'asc'=>'tag.name',
                                            'match'=>false      //Post with no tags should be displayed too
                                        )
                                );

            $data['tags'] = array();
            foreach($data['post']->Tag as  $t){
                $data['tags'][] = $t->name;
            }
            $data['tags'] = implode(', ', $data['tags']);
            
        }catch(Exception $e){
            //Exception will be thrown if Post not found
            return array('/error/postNotFound/'.$p->id,'internal');
        }
        
        $this->renderAction('admin_edit_post');
	}

    function listUser(){
        Doo::loadHelper('DooPager');
        Doo::loadModel('User');

        $u = new User();
        //if default, no sorting defined by user, show this as pager link
        $page_size = $this->getPageSize();
        $pages = Doo::conf()->PAGES;

        if($this->sortField=='email' && $this->orderType=='desc'){
            $pager = new DooPager(Doo::conf()->APP_URL.'admin/users/page', $u->count(), $page_size, $pages);
        }else{
            $pager = new DooPager(Doo::conf()->APP_URL."admin/users/sort/$this->sortField/$this->orderType/page", $u->count(), $page_size, $pages);
        }

        if(isset($this->params['pindex']))
            $pager->paginate(intval($this->params['pindex']));
        else
            $pager->paginate(1);

        $this->data['pager'] = $pager->output;

        $columns = 'id,email,first_name,last_name,first_name_alphabet,last_name_alphabet,phone,qq,type,confirm_code,status';
        //Order by ASC or DESC
        if($this->orderType=='desc'){
            $this->data['users'] = $u->limit($pager->limit, null, $this->sortField,
                                        //we don't want to select the Content (waste of resources)
                                        array('select'=>$columns)
                                  );
            $this->data['orderType'] = 'asc';
        }else{
            $this->data['users'] = $u->limit($pager->limit, $this->sortField, null,
                                        //we don't want to select the Content (waste of resources)
                                        array('select'=>$columns)
                                  );
            $this->data['orderType'] = 'desc';
        }

        $this->renderAction('/admin/user/index');
    }


}
?>
