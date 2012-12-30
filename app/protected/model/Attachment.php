<?php
Doo::loadCore('db/DooSmartModel');

class Attachment extends DooSmartModel{

    public $id;
    public $application_id;
    public $file_name;
    public $file_size;
    public $file_type;
    public $content;
    public $title;
    public $_table = 'attachment';
    public $_primarykey = 'id';
    public $_fields = array('id','application_id','file_name','file_size','file_type','content','title');
    function __construct(){
        parent::$className = __CLASS__;     //a must if you are using static querying methods Application::_count(), Application::getById()
    }
}
?>
