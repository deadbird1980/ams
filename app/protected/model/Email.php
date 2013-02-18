<?php
Doo::loadCore('db/DooSmartModel');

class Email extends DooSmartModel{

    public $id;
    public $user_id;
    public $to;
    public $bcc;
    public $subject;
    public $body;
    public $sent;
    public $_table = 'email';
    public $_primarykey = 'id';
    public $_fields = array('id','user_id','to','bcc','subject','body', 'sent');
    function __construct(){
        parent::$className = __CLASS__;     //a must if you are using static querying methods Application::_count(), Application::getById()
    }

}
?>
