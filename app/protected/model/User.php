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

    const ADMIN = 'admin';
    const COUNSELOR = 'counselor';
    const EXECUTOR = 'executor';
    const CUSTOMER = 'customer';
    //status
    const REGISTERED = 'registered';
    const ACTIVE = 'active';
    const OBSOLETE = 'obsolete';
    const INACTIVE = 'inactive';

    public function __construct($properties=null){
        parent::$className = __CLASS__;     //a must if you are using static querying methods Food::_count(), Food::getById()
        parent::__construct($properties);
    }

    public static function register($data) {
        $u = new User($data);
        $u->setPassword($data['password']);
        $u->type = 'customer';
        $u->status = 'registered';
        // calculate confirm key
        $u->confirm_code = md5($u->email . '@' . Doo::conf()->SITE_ID.'@' . time());
        $u->insert();
        return $u;
    }

    public function scopeSeenByMe() {
        if ($this->isAdmin()) {
            return array('where'=>'1=1');
        } elseif ($this->isCounselor() || $this->isExecutor()) {
            return array('where'=>'user.status<>\'inactive\' and activated_by='.$this->id);
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
      return $this->type == User::ADMIN;
    }

    public function isExecutor() {
      return $this->type == User::EXECUTOR;
    }

    public function isCounselor() {
      return $this->type == User::COUNSELOR;
    }

    public function isCustomer() {
      return $this->type == User::CUSTOMER;
    }

    public function isRegistered() {
        if ($this->status == User::REGISTERED) {
            return true;
        }
        return false;
    }

    public function activate($user) {
        if ($this->isRegistered()) {
            $this->status = User::ACTIVE;
            $this->activated_by = $user->id;
            $this->update();
            return true;
        }
        return false;
    }

    public function isActive() {
        return $this->status == User::ACTIVE;
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

    public function hasApplicationsToConfirm() {
        Doo::loadModel('Application');
        $app = new Application();
        return $app->count(array('where'=>"assignee_id={$this->id} and status='".Application::SUBMITTED."'")) > 0;
    }

    public function toDo() {
        if ($this->hasApplicationsToFillIn()) {
            return 'applications_to_fill_in';
        } else if ($this->hasApplicationsToConfirm()) {
            return 'applications_to_confirm';
        }
        return '';
    }

    public function applicationCount() {
        $app = Doo::loadModel('Application', true);
        return $app->count(array('where'=>"user_id={$this->id}"));
    }

    public function destroy(){
        $this->status = User::INACTIVE;
        $apps = $this->applications();
        foreach($apps as $app) {
            $app->archive();
        }
        $this->update();
    }

    public function applications() {
        $app = Doo::loadModel('Application', true);
        return $app->find(array('where'=>"user_id={$this->id}"));
    }

    public function setPassword($password) {
        $this->password = md5($this->email . '@' . Doo::conf()->SITE_ID.'@'.$password);
    }

    private function encryptPassword($password) {
        return md5($this->email . '@' . Doo::conf()->SITE_ID.'@'.$password);
    }

    public function confirmPassword($password) {
        if ($this->password == $password || $this->password == $this->encryptPassword($password)) {
            return true;
        }
        return false;
    }

    public function fullName() {
        return "{$this->last_name}{$this->first_name}";
    }
}
?>
