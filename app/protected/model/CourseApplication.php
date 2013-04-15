<?php
Doo::loadCore('db/DooSmartModel');
Doo::loadCore('db/DooDbExpression');
Doo::loadModel('Application');
Doo::loadModel('Attachment');
Doo::loadModel('SchoolApplication');

class CourseApplication extends DooSmartModel{

    public $id;
    public $application_id;
    public $school;
    public $subject;
    public $course;
    public $status;
    public $sent;
    public $replied;
    public $result;
    public $resent;
    public $done;
    public $_table = 'course_application';
    public $_primarykey = 'id';
    public $_fields = array('id','application_id','school','subject','course','status','sent','replied','result','resent','done');
    //status
    const SUBMITTED = 'submitted';
    const CONFIRMED = 'confirmed';
    const SENT = 'sent';
    const REPLIED = 'replied';
    const CHOSEN = 'chosen';
    const RECONFIRMED = 'reconfirmed';
    const RESENT = 'resent';
    const DONE = 'done';

    //result
    const APPROVED = 'approved';
    const CONDITION_APPROVED = 'condition_approved';
    const REFUSED = 'refused';

    public function __construct($properties=null){
        parent::$className = __CLASS__;     //a must if you are using static querying methods Application::_count(), Application::getById()
        parent::__construct($properties);
    }

    public function isSubmitted() {
        return $this->application()->isSubmitted();
    }

    public function isConfirmed() {
        return $this->application()->isConfirmed();
    }

    public function isSent() {
        return $this->status == CourseApplication::SENT;
    }

    public function isReplied() {
        return $this->status == CourseApplication::REPLIED;
    }

    public function isChosen() {
        return $this->status == CourseApplication::CHOSEN;
    }

    public function isReConfirmed() {
        return $this->status == CourseApplication::RECONFIRMED;
    }

    public function isResent() {
        return $this->status == CourseApplication::RESENT;
    }


    public function isDone() {
        return $this->status == CourseApplication::DONE;
    }

    public function send() {
        if ($this->status == 'sent') {
            return;
        }
        $this->status = 'sent';
        $this->sent = new DooDbExpression('NOW()');
        $this->update();
    }

    public function resend() {
        if ($this->status == 'resent') {
            return;
        }
        $this->status = 'resent';
        $this->resent = new DooDbExpression('NOW()');
        $this->update();
    }

    public function todo() {
        if ($this->isDone()) {
            return 'files';
        } elseif ($this->isSent()) {
            return 'reply';
        } elseif ($this->isReplied()) {
            if ($this->result == CourseApplication::APPROVED) {
                return 'choose';
            } elseif ($this->result == CourseApplication::REFUSED) {
                return 'finish';
            } elseif ($this->result == CourseApplication::CONDITION_APPROVED) {
                return 'choose';
            }
        } elseif ($this->isChosen()) {
            return 'reconfirm';
        } elseif ($this->isReConfirmed()) {
            return 'resend';
        } elseif ($this->isResent()) {
            return 'finish';
        } elseif ($this->isConfirmed()) {
            return 'send';
        }
        return '';
    }

    public function reply($result) {
        if ($this->isReplied()) {
            return false;
        }
        $this->result = $result;
        $this->status = CourseApplication::REPLIED;
        $this->replied = new DooDbExpression('NOW()');
        return $this->update();
    }

    public function finish($result) {
        if ($this->isDone()) {
            return false;
        }
        $this->result = $result;
        $this->status = CourseApplication::DONE;
        $this->done = new DooDbExpression('NOW()');
        //update application
        $this->application()->finish();
        return $this->update();
    }

    public function choose() {
        if ($this->isChosen()) {
            return false;
        }
        $this->status = CourseApplication::CHOSEN;
        foreach($this->siblings() as $app) {
            $app->status = CourseApplication::DONE;
            $app->update();
        }
        $this->application()->resubmit();
        return $this->update();
    }

    public function reconfirm() {
        $this->status = CourseApplication::RECONFIRMED;
        return $this->update();
    }

    public function siblings() {
        return $this->find(array('where'=>"application_id={$this->application_id} and id<>{$this->id}"));
    }

    public function attachment() {
        if ($this->isReplied()) {
            $a = Doo::loadModel('CourseApplicationAttachment', true);
            $a = $a->getByType__Course_application_id_first('reply', $this->id);
            if ($a) {
                return $a;
            }
        }
        $a = new StdClass();
        $a->id = null;
        return $a;
    }

    public function application() {
        if (!isset($this->application)) {
            $app = Doo::loadModel('Application', true);
            $this->application = $app->getById_first($this->application_id);
        }
        return $this->application;
    }

    public function canBeSeen($user) {
        return $this->application()->canBeSeen($user);
    }
}
?>
