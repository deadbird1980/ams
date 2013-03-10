<?php
Doo::loadCore('db/DooSmartModel');

class ApplicationFile extends DooSmartModel {

    public $id;
    public $application_type;
    public $name;
    public $mandatory;
    public $_table = 'application_file';
    public $_primarykey = 'id';
    public $_fields = array('id','application_type','name','mandatory');

    public function __construct($properties=null){
        parent::$className = __CLASS__;     //a must if you are using static querying methods Application::_count(), Application::getById()
        parent::__construct($properties);
        //parent::setupModel(__CLASS__);
    }
}
?>
