<?php
Doo::loadCore('db/DooSmartModel');

class VisaApplication extends DooSmartModel{

    public $id;
    public $application_id;
    public $country;
    public $start_date;
    public $passport_no;
    public $passport_name;
    public $birthday;
    public $organization;
    public $passport_start_date;
    public $passport_end_date;
    public $visa_start_date;
    public $visa_end_date;
    public $address;
    public $_table = 'user';
    public $_primarykey = 'id';
    public $_fields = array('id','application_id','country','start_date','passport_no','passport_name','birthday','organization','passport_start_date','passport_end_date','visa_start_date','visa_end_date','address');
    function __construct(){
        parent::$className = __CLASS__;     //a must if you are using static querying methods Application::_count(), Application::getById()
    }
}
?>
