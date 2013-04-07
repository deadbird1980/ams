<?php
Doo::loadCore('db/DooSmartModel');

class CourseApplicationAttachment extends DooSmartModel{

    public $id;
    public $course_application_id;
    public $type;
    public $attachment_id;

    public $application_id;
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
            } elseif (in_array($key, $this->application_file->_fields)) {
                return $this->application_file->$key;
            }
        }
        return parent::__get($key);
    }

    protected function setAttachment() {
        $att = Doo::loadModel('Attachment', true);
        $this->attachment = $att->getById_first($this->attachment_id);
    }

    public function fileExist($file) {
        $fnd = $this->db()->find($this, array('limit'=>1));
        if ($fnd) {
            return true;
        }
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

    public function sameGroup() {
        return $this->find(array('where'=>"course_application_id={$this->application_id}"));
    }

    public function getGroupPath() {
        return "{$this->application_id}/{$this->course_application_id}";
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
