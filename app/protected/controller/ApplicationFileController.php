<?php
require_once 'BaseController.php';

class ApplicationFileController extends BaseController {

    protected $user;
    protected $sortField = 'application_type';
    protected $orderType = 'desc';
    protected $helper = 'ApplicationFileHelper';

	public function index() {
        Doo::loadHelper('DooPager');
        Doo::loadModel('ApplicationFile');
        $appFile = new ApplicationFile();
        // operations
        if ($this->isPost() && isset($_POST['operation'])) {
            if ($_POST['operation'] == 'delete') {
                foreach($_POST['application_files'] as $app_id) {
                    $app2delete = $app->getById_first($app_id);
                    $app2delete->delete();
                }
                $this->data['message'] = $this->t('item_deleted');
            }
        }

        if (($count = $appFile->count()) > 0) {
            if (isset($this->params['sortField'])) {
                $this->sortField = $this->params['sortField'];
            }
            if (isset($this->params['orderType'])) {
                $this->orderType = $this->params['orderType'];
            }
            $page_size = $this->getPageSize();
            $pages = $this->getPages();

            $url = Doo::conf()->APP_URL.'admin/application_files/page';

            if($this->sortField=='application_type' && $this->orderType=='desc'){
                $pager = new DooPager($url, $count, $page_size, $pages);
            }else{
                $pager = new DooPager(Doo::conf()->APP_URL."admin/application_files/sort/{$this->sortField}/{$this->orderType}/page", $count, $page_size, $pages);
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
                $this->data['applicationFiles'] = $appFile->limit($pager->limit, $this->sortField, null);
            }else{
                $options['desc'] = $this->sortField;
                $this->data['order'] = 'desc';
                $this->data['orderType'] = 'asc';
                $this->data['applicationFiles'] = $appFile->limit($pager->limit, null, $this->sortField);
            }
            $this->data['sortField'] = $this->sortField;
        }

        $this->renderAction('/admin/application_file/index');
	}

    public function edit() {
        Doo::loadModel('ApplicationFile');

        $appFile = new ApplicationFile();
        $this->data['applicationFile'] = $appFile;
        if (isset($this->params['id'])) {
            $this->data['application'] = $appFile->getById_first($this->params['id']);
        }
        $form = $this->helper->getApplicationFileForm($appFile);

        if ($this->isPost() && $form->isValid($_POST)) {
            $id = $this->params['id'];
            $appFile = new ApplicationFile($_POST);
            if ($id) {
              $appFile->update_attributes($_POST, array('where'=>"id=${id}"));
            } else {
              $id = $appFile->insert();
            }
            return Doo::conf()->APP_URL . "index.php/admin/application_files/{$id}/edit";
        }
        $this->data['form'] = $form->render();
        $this->renderAction('/admin/application_file/edit');
    }
}
?>
