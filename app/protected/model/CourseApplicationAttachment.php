<?php
Doo::loadCore('db/DooSmartModel');

class CourseApplicationAttachment extends DooSmartModel{

    public $id;
    public $course_application_id;
    public $type;
    public $attachment_id;

    public $application_id;
    public $course_application;

    protected $attachment;

    public $group_field = 'course_application_id';
    public $_table = 'course_application_attachment';
    public $_primarykey = 'id';
    public $_fields = array('id','course_application_id','attachment_id','type');

    public function __construct($properties=null){
        parent::$className = __CLASS__;     //a must if you are using static querying methods Application::_count(), Application::getById()
    }

    public function __get($key) {
        if (!in_array($key, $this->_fields)) {
            if (!$this->attachment) {
                $this->setAttachment();
            }
            if (in_array($key, array('attachment'))) {
                return $this->$key;
            }
            if ($this->attachment && in_array($key, $this->attachment->_fields)) {
                return $this->attachment->$key;
            } elseif (in_array($key, $this->course_application->_fields)) {
                return $this->course_application->$key;
            }
        }
        return parent::__get($key);
    }

    protected function setCourseApplication() {
        $att = Doo::loadModel('CourseApplication', true);
        $this->course_application = $att->getById_first($this->course_application_id);
    }

    protected function setAttachment() {
        $att = Doo::loadModel('Attachment', true);
        $this->attachment = $att->getById_first($this->attachment_id);
    }

    public function fileExist($file) {
        return false;
    }

    public function importFile($file) {
        $attachment = Doo::loadModel('Attachment', true);
        $file = $attachment->importFile($file);
        $this->attachment_id = $file->id;
        $file->course_application_id = $this->course_application_id;
        $file->id = $this->insert();
        return $file;
    }

    public function sameGroup($type=null) {
        $where = "course_application_id={$this->course_application_id}";
        if (isset($type)) {
            $where .= " and type='$type'";
        }
        return $this->find(array('where'=>$where));
    }

    public function getGroupPath() {
        if (!$this->course_application) {
            $this->setCourseApplication();
        }
        return "{$this->course_application->application_id}/{$this->course_application_id}";
    }

    public function remove() {
        $this->setAttachment();
        $this->beginTransaction();
        $att = $this->attachment;
        if ($this->delete() && $att->delete()) {
            $this->commit();
            return true;
        }
        $this->rollBack();
        return false;;
    }
}
?>
