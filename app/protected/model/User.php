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
    public $status;
    public $activated_by;
    public $_table = 'user';
    public $_primarykey = 'id';
    public $_fields = array('id','email','password','type','first_name','last_name','first_name_alphabet','last_name_alphabet','phone','qq','confirm_code','status','activated_by');
    public function __construct($properties=null){
        parent::$className = __CLASS__;     //a must if you are using static querying methods Food::_count(), Food::getById()
        parent::__construct($properties);
    }

    public function scopeSeenByMe() {
        if ($this->isAdmin()) {
            return array();
        } elseif ($this->isCounselor() || $this->isExecutor()) {
            return array('where'=>'activated_by='.$this->id);
        } elseif ($this->isCustomer()) {
            return array('where'=>'id='.$this->id);
        }
        return array();
    }

    public function isAdmin() {
      return $this->type == 'admin';
    }

    public function isExecutor() {
      return $this->type == 'executor';
    }

    public function isCounselor() {
      return $this->type == 'counselor';
    }

    public function isCustomer() {
      return $this->type == 'customer';
    }

    public function isRegistered() {
        if ($this->status == 'registered') {
            return true;
        }
        return false;
    }

    public function activate($user_id) {
        if ($this->status == 'registered') {
            $this->status = 'active';
            $this->activated_by = $user_id;
            $this->update();
            return true;
        }
        return false;
    }

}
?>
