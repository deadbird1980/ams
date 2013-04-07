<?php
Doo::loadCore('db/DooSmartModel');
Doo::loadModel('ApplicationFile');

class CourseApplicationAttachment extends DooSmartModel{

    public $id;
    public $course_application_id;
    public $attachment_id;
    public $type; //basic/reply/additional

    public $_table = 'course_application_attachment';
    public $_primarykey = 'id';
    public $_fields = array('id','course_application_id','attachment_id','type');

    public function __construct($properties=null){
        parent::$className = __CLASS__;     //a must if you are using static querying methods Application::_count(), Application::getById()
    }

    public function fileExist($file) {
        $a = new Attachment();
        $a->application_id = $this->application_id;
        if (isset($file->application_file)) {
            $a->application_file_id = $file->application_file;
        } else {
            $a->file_name = $file->name;
        }
        $fnd = $this->db()->find($a, array('limit'=>1));
        if ($fnd) {
            return true;
        }
        return false;
    }

    public function importFile($file) {
        $attachment = Doo::loadModel('Attachment', true);
        $file = $attachment->importFile($file);
        $this->attachment_id = $attachment->id;
        $this->save();
        $file->id = $this->id;
        return $file;
    }

    public function sameApplication() {
        return $this->find(array('where'=>"application_id={$this->application_id}"));
    }

    public function getApplicationPath() {
        return "{$this->application_id}/{$this->id}";
    }

    public function destroy() {
        $att = Doo::loadModel('Attachment', true);
        $att = $att->getById_first($this->attachment_id);
        if ($att->delete()) {
            $this->delete();
        }
    }
}
?>
