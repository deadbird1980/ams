<?php
Doo::loadCore('db/DooSmartModel');
Doo::loadModel('ApplicationFile');

class Attachment extends DooSmartModel{

    public $id;
    public $application_id;
    public $application_file_id;
    public $file_name;
    public $file_size;
    public $file_type;
    public $title;
    public $_table = 'attachment';
    public $_primarykey = 'id';
    public $_fields = array('id','application_id','application_file_id','file_name','file_size','file_type','title');

    public function __construct($properties=null){
        parent::$className = __CLASS__;     //a must if you are using static querying methods Application::_count(), Application::getById()
    }

    public function fileExist($file) {
        $a = new Attachment();
        $a->application_id = $this->application_id;
        $a->application_file_id = $file->application_file;
        $fnd = $this->db()->find($a, array('limit'=>1));
        if ($fnd) {
            return true;
        }
        return false;
    }

    public function importFile($file) {
        $this->file_name = $file->name;
        $this->file_size = $file->size;
        $this->file_type = $file->type;
        $this->application_file_id = $file->application_file;
        // replace id with name for display
        $af = new ApplicationFile();
        $af = $af->getById_first($this->application_file_id);
        $file->application_file = $af->name;
        $file->mandatory = $af->mandatory;
        $file->application_file_id = $this->application_file_id;
        $file->id = $this->insert();
        return $file;
    }

    public function sameApplication() {
        return $this->find(array('where'=>"application_id={$this->application_id}"));
    }
}
?>
