<?php
Doo::loadCore('db/DooSmartModel');

class Attachment extends DooSmartModel{

    public $id;
    public $application_id;
    public $file_name;
    public $file_size;
    public $file_type;
    public $title;
    public $_table = 'attachment';
    public $_primarykey = 'id';
    public $_fields = array('id','application_id','file_name','file_size','file_type','title');
    function __construct(){
        parent::$className = __CLASS__;     //a must if you are using static querying methods Application::_count(), Application::getById()
    }

    public function importFile($file) {
        $this->file_name = $file->name;
        $this->file_size = $file->size;
        $this->file_type = $file->type;
        return $this->insert();
    }
}
?>
