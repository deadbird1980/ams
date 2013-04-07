<?php
Doo::loadCore('db/DooSmartModel');
Doo::loadModel('ApplicationFile');

class ApplicationAttachment extends DooSmartModel{

    public $id;
    public $application_id;
    public $application_file_id;
    public $attachment_id;

    protected $attachment;
    protected $application_file;

    public $group_field = 'application_id';
    public $_table = 'application_attachment';
    public $_primarykey = 'id';
    public $_fields = array('id','application_id','application_file_id','attachment_id');

    public function __construct($properties=null){
        parent::$className = __CLASS__;     //a must if you are using static querying methods Application::_count(), Application::getById()
    }

    public function __get($key) {
        if (!in_array($key, $this->_fields)) {
            if (!$this->attachment) {
                $this->setAttachment();
            }
            if (!$this->application_file) {
                $this->setApplicationFile();
            }
            if (in_array($key, array('application_file', 'attachment'))) {
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

    protected function setApplicationFile() {
        $att = Doo::loadModel('ApplicationFile', true);
        $this->application_file = $att->getById_first($this->application_file_id);
    }

    public function fileExist($file) {
        $a = new ApplicationAttachment();
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
        if (isset($file->application_file)) {
            $this->application_file_id = $file->application_file;
            $af = $this->application_file;
            $file->application_file = $af->name;
            $file->mandatory = $af->mandatory;
            $file->application_file_id = $this->application_file_id;
        }
        $this->attachment_id = $file->id;
        $file->id = $this->insert();
        return $file;
    }

    public function sameGroup() {
        return $this->find(array('where'=>"application_id={$this->application_id}"));
    }

    public function getGroupPath() {
        return "{$this->application_id}";
    }

    public function remove() {
        $this->setAttachment();
        $this->beginTransaction();
        $att = $this->attachment;
        if ($this->delete() && $att->delete()) {
        //if ($this->attachment->delete() && $this->delete()) {
            $this->commit();
            return true;
        }
        $this->rollBack();
        return false;;
    }
}
?>
