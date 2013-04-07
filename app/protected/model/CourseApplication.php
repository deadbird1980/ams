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
    public $resent;
    public $done;
    public $_table = 'course_application';
    public $_primarykey = 'id';
    public $_fields = array('id','application_id','school','subject','course','status','sent','replied','approved_document_id','resent','done');
    //status
    const SUBMITTED = 'submitted';
    const CONFIRMED = 'confirmed';
    const SENT = 'sent';
    const REPLIED = 'replied';
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
        return $this->status == CourseApplication::SUBMITTED;
    }

    public function isConfirmed() {
        return $this->status == CourseApplication::CONFIRMED;
    }

    public function isSent() {
        return $this->status == CourseApplication::SENT;
    }

    public function isReplied() {
        return $this->status == CourseApplication::REPLIED;
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

    public function todo() {
        if ($this->isSubmitted()) {
            return 'confirm';
        } elseif ($this->isConfirmed()) {
            return 'send';
        } elseif ($this->isSent()) {
            return 'reply';
        } elseif ($this->isReplied()) {
        }
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

    public function attachments() {
        $a = new Attachment();
        return $a->getByApplication_id__type($this->application_id, 'reply');
    }
}
?>
