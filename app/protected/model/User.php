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
            return array('where'=>'1=1');
        } elseif ($this->isCounselor() || $this->isExecutor()) {
            return array('where'=>'activated_by='.$this->id);
        } elseif ($this->isCustomer()) {
            return array('where'=>'id='.$this->id);
        }
        return array();
    }

    public function isAvailabeTo($user) {
        if ($user->isAdmin()) {
            return true;
        } elseif ($user->isCounselor() || $user->isExecutor()) {
            return $this->activated_by == $user->id;
        } elseif ($this->isCustomer()) {
            return $this->id == $user->id;
        }
        return false;
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

    public function shouldHaveCustomers() {
        return $this->isCounselor() || $this->isExecutor();
    }

    public function hasCustomers() {
        return $this->count(array('where' => "activated_by={$this->id}")) > 0;
    }

    public function hasApplicationsToFillIn() {
        Doo::loadModel('Application');
        $app = new Application();
        return $app->count(array('where'=>"user_id={$this->id} and status='".Application::CREATED."'")) > 0;
    }

    public function delete($opt=NULL){
        if ($apps = $this->relateApplication()) {
            foreach($apps as $app) {
                $app->delete();
            }
        }
        parent::delete($opt);
    }

}
?>
