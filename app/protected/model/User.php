<?php
Doo::loadCore('db/DooSmartModel');

class User extends DooSmartModel{

    public $id;
    public $email;
    public $password;
    public $type; // customer/counselor/executor/admin
    public $first_name;
    public $last_name;
    public $first_name_alphabet;
    public $last_name_alphabet;
    public $phone;
    public $qq;
    public $confirm_code;
    public $_table = 'user';
    public $_primarykey = 'id';
    public $_fields = array('id','email','password','type','first_name','last_name','first_name_alphabet','last_name_alphabet','phone','qq','confirm_code');
    function __construct(){
        parent::$className = __CLASS__;     //a must if you are using static querying methods Food::_count(), Food::getById()
    }

    function isAdmin() {
      return $this->type == 'admin';
    }

    function isStaff() {
      return $this->type == 'staff';
    }

    function isUser() {
      return $this->type == 'user';
    }
}
?>
