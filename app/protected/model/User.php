<?php
Doo::loadCore('db/DooSmartModel');

class User extends DooSmartModel{

    public $id;
    public $username;
    public $pwd;
    public $group;
    public $first_name;
    public $last_name;
    public $first_name_alphabet;
    public $last_name_alphabet;
    public $_table = 'user';
    public $_primarykey = 'id';
    public $_fields = array('id','username','pwd','group','first_name','last_name','first_name_alphabet','last_name_alphabet','address');
    function __construct(){
        parent::$className = __CLASS__;     //a must if you are using static querying methods Food::_count(), Food::getById()
    }
}
?>
