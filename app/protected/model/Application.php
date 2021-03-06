<?php
Doo::loadCore('db/DooSmartModel');
Doo::loadCore('db/DooDbExpression');
Doo::loadClass('ApplicationType');
Doo::loadModel('SchoolApplication');
Doo::loadModel('VisaApplication');

class Application extends DooSmartModel {

    public $id;
    public $user_id;
    public $type;
    public $status; // in_progress/submitted/confirmed/returned
    public $assignee_id;
    public $executor_id;
    public $start_date;
    public $end_date;
    public $paid;
    public $submitted;
    public $confirmed;
    public $rejected;
    public $comment;
    public $created;
    public $updated;

    public $detail;
    public $readonly = false;
    public $_table = 'application';
    public $_primarykey = 'id';
    public $_fields = array('id','user_id','type','status','assignee_id','executor_id','start_date','end_date','paid','submitted','confirmed','rejected','comment','created','updated');

    //status
    const CREATED = 'created';
    const IN_PROGRESS = 'in_progress';
    const SUBMITTED = 'submitted';
    const CONFIRMED = 'confirmed';
    const SENT = 'sent';
    const REPLIED = 'replied';
    const REJECTED = 'rejected';
    const RESUBMITTED = 'resubmitted';
    const DONE = 'done';
    const ARCHIVE = 'archive';

    public function __construct($properties=null){
        parent::$className = __CLASS__;     //a must if you are using static querying methods Application::_count(), Application::getById()
        parent::__construct($properties);
        //parent::setupModel(__CLASS__);
    }

    public function scopeSeenByUser($user) {
        if ($user->isAdmin()) {
            return array('where'=>'1=1');
        } elseif ($user->isCounselor()) {
            return array('where'=>"application.status<>'archive' and assignee_id={$user->id}");
        } elseif ($user->isExecutor()) {
            return array('where'=>"(application.status<>'archive' and (application.status='submitted' or application.executor_id={$user->id}))");
        } elseif ($user->isCustomer()) {
            return array('where'=>'user_id='.$user->id);
        }
        return array();
    }

    public function canBeSeen($user) {
        if ($user->isAdmin()) {
            return true;
        } elseif ($user->isCounselor()) {
            return $this->assignee_id == $user->id;
        } elseif ($user->isExecutor()) {
            return $this->isSubmitted() || $this->executor_id == $user->id;
        } elseif ($user->isCustomer()) {
            return $this->user_id= $user->id;
        }
        return false;
    }

    public function canBeModified($user) {
        if ($user->isAdmin()) {
            return true;
        } elseif ($user->isCounselor()) {
            return $this->assignee_id == $user->id && ($this->beforeSubmitted() || $this->isRejected());
        } elseif ($user->isExecutor() && $this->isSubmitted()) {
            return true;
        } elseif ($user->isCustomer()) {
            return $this->user_id= $user->id && $this->beforeSubmitted() && !$this->isRejected();
        }
        return false;
    }

    public function isSubmitted() {
        return $this->status == Application::SUBMITTED || $this->status == Application::RESUBMITTED;
    }

    public function isResubmitted() {
        return $this->status == Application::RESUBMITTED;
    }

    public function isConfirmed() {
        return $this->status == Application::CONFIRMED;
    }

    public function isRejected() {
        return $this->status == Application::REJECTED;
    }

    public function afterSubmitted() {
        return !$this->beforeSubmitted();
    }

    public function beforeSubmitted() {
        return $this->status == Application::IN_PROGRESS || $this->status == Application::CREATED || $this->status == Application::REJECTED;
    }

    public function isCreated() {
        return $this->status == Application::CREATED;
    }

    public function getDetail() {
        if (!isset($this->detail)) {
            if (ApplicationType::isVisa($this->type)) {
                $a = new VisaApplication();
            } else {
                $a = new SchoolApplication();
            }
            $this->detail = $a->getByid_first($this->id);
        }
        return $this->detail;
    }

    public function delete($opt=NULL){
        if ($detail = $this->getDetail()) {
            $detail->delete();
        }
        parent::delete($opt);
    }

    public function createDetailApplication($data=null) {
        if (ApplicationType::isVisa($this->type)) {
            $a = new VisaApplication();
            if (!($a = $a->getByid_first($this->id))) {
                $a = new VisaApplication($data);
                $a->id = $this->id;
                $a->application_id = $this->id;
                $a->insert();
            }
        } else {
            $a = new SchoolApplication();
            if (!$this->id || !($a = $a->relateCourseApplication_first(array('where'=>'school_application.id='.$this->id)))) {
                $a = new SchoolApplication($data);
                $a->id = $this->id;
                $a->application_id = $this->id;
                $a->insert();
                $a->createDetailApplication($data);
            }
        }
        return $a;
    }

    public function create($hash) {
        if ($id = $this->insert()) {
            $this->id = $id;
            if ($this->isSchool()) {
                $app_detail = $this->createDetailApplication($hash);
            }
            return $id;
        }
        return -1;
    }

    public function isVisa() {
        return ApplicationType::isVisa($this->type);
    }

    public function isSchool() {
        return ApplicationType::isSchool($this->type);
    }

    public function paid() {
        if (!$this->paid) {
            $this->paid = 1;
            $this->update();
        }
        return true;
    }

    public function canChangeTo($type) {
        if (!$this->isCreated()) {
            return false;
        }
        if ((ApplicationType::isVisa($this->type) && ApplicationType::isVisa($type)) ||
            (ApplicationType::isSchool($this->type) && ApplicationType::isSchool($type))) {
                return true;
            }
        return false;
    }

    public function export() {
    }

    public function todo() {
        if ($this->isSubmitted()) {
            return 'confirm';
        } elseif ($this->isResubmitted()) {
            return 'confirm';
        }
        return '';
    }

    public function filesEssential() {
        return $this->applicationFiles(true);
    }

    public function applicationFiles($required=false) {
        Doo::loadModel('ApplicationFile');
        $appFile = new ApplicationFile();
        $where = "application_type='{$this->type}'";
        if ($required) {
            $where .= " and mandatory=1";
        }
        $options = array('where'=>$where);
        $rtn = $appFile->find($options);
        return $rtn;
    }

    public function attachments() {
        $aa = Doo::loadModel('ApplicationAttachment', true);
        return $aa->find(array('where'=>"application_id={$this->id}"));
    }

    public function confirm($user) {
        $this->status = Application::CONFIRMED;
        $this->confirmed = new DooDbExpression('NOW()');
        $this->executor_id = $user->id;
        $this->update();
    }

    public function reject($user, $comment) {
        $this->status = Application::REJECTED;
        $this->confirmed = new DooDbExpression('NULL');
        $this->rejected = new DooDbExpression('NOW()');
        $this->executor_id = $user->id;
        $this->comment = $comment;
        $this->update();
    }

    public function archive() {
        $this->status = Application::ARCHIVE;
        $this->update();
    }

    public function isFilesReady() {
        foreach($this->attachments() as $a) {
            $ids[] = $a->application_file_id;
        }
        foreach($this->filesEssential()  as $file) {
            if (!in_array($file->id, $ids)) {
                return false;
            }
        }
        return true;
    }

    public function submit() {
        if ($this->isSubmitted()) {
            return;
        }
        $this->status = Application::SUBMITTED;
        $this->start_date = new DooDbExpression('NOW()');
        $this->update();
        return true;
    }

    public function resubmit() {
        if ($this->isResubmitted()) {
            return;
        }
        $this->status = Application::RESUBMITTED;
        $this->update();
        return true;
    }

    public function finish() {
        $this->status = CourseApplication::DONE;
        $this->end_date = new DooDbExpression('NOW()');
        return $this->update();
    }

    public function assignee() {
        $u = Doo::loadModel('User', true);
        return $u->getById_first($this->assignee_id);
    }

    public function executor() {
        $u = Doo::loadModel('User', true);
        return $u->getById_first($this->executor_id);
    }

    public function chosen() {
        $u = Doo::loadModel('CourseApplication', true);
        return $u->find(array('where'=>"application_id={$this->id} and status='chosen'", 'limit'=>1));
    }

    public function needToSend() {
        return $this->relateUser(array('where'=>"application.status='submitted' and application.started+interval 1 day < now()"));
    }

    public function send() {
        $this->status = CourseApplication::SENT;
        return $this->update();
    }

    public function updateType($hash) {
        if ($this->isSchool()) {
        } else {
        }
        if ($this->type != $hash['type']) {
            $this->type = $hash['type'];
            $this->update();
        }
    }

    public function user() {
        $u = Doo::loadModel('User', true);
        return $u->getById_first($this->user_id);
    }

}
?>
