<?php
Doo::loadCore('db/DooSmartModel');
Doo::loadClass('ApplicationType');
Doo::loadModel('SchoolApplication');

class Application extends DooSmartModel{

    public $id;
    public $user_id;
    public $type;
    public $status; // in_progress/submitted
    public $assignee_id;
    public $start_date;
    public $end_date;
    public $paid;
    public $_table = 'application';
    public $_primarykey = 'id';
    public $_fields = array('id','user_id','type','status','assignee_id','start_date','end_date','paid');

    //status
    const CREATED = 'created';
    const IN_PROGRESS = 'in_progress';
    const SUBMITTED = 'submitted';
    const CONFIRMED = 'confirmed';
    const SENT = 'sent';
    const REPLIED = 'replied';
    const DONE = 'done';

    public function __construct($properties=null){
        parent::$className = __CLASS__;     //a must if you are using static querying methods Application::_count(), Application::getById()
        parent::__construct($properties);
        //parent::setupModel(__CLASS__);
    }

    public function scopeSeenByUser($user) {
        if ($user->isAdmin()) {
            return array();
        } elseif ($user->isCounselor() || $user->isExecutor()) {
            return array('where'=>'assignee_id='.$user->id);
        } elseif ($user->isCustomer()) {
            return array('where'=>'user_id='.$user->id);
        }
        return array();
    }

    public function isSubmitted() {
        return $this->status == 'submitted';
    }

    public function createDetailApplication() {
        if (ApplicationType::isVisa($this->type)) {
            $a = new VisaApplication();
            if (!($a = $a->getByid($this->id))) {
                $a = new VisaApplication();
                $a->id = $this->id;
                $a->application_id = $this->id;
                $a->insert();
            }
        } else {
            $a = new SchoolApplication();
            if (!($a = $a->getByid($this->id))) {
                $a = new SchoolApplication();
                $a->id = $this->id;
                $a->application_id = $this->id;
                $a->insert();
            }
        }
        return $a;
    }
}
?>
