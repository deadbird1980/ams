<?php
Doo::loadCore('db/DooSmartModel');

class Application extends DooSmartModel{

    public $id;
    public $user_id;
    public $type;
    public $status;
    public $start_date;
    public $end_date;
    public $paid;
    public $_table = 'application';
    public $_primarykey = 'id';
    public $_fields = array('id','user_id','type','status','start_date','end_date','paid');
    function __construct(){
        parent::$className = __CLASS__;     //a must if you are using static querying methods Application::_count(), Application::getById()
    }
}
?>
