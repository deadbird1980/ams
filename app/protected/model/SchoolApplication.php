<?php
Doo::loadCore('db/DooSmartModel');
Doo::loadModel('CourseApplication');

class SchoolApplication extends DooSmartModel{

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
    public $_table = 'school_application';
    public $_primarykey = 'id';
    public $_fields = array('id','application_id','country','start_date','passport_no','passport_name','birthday','organization','passport_start_date','passport_end_date','visa_start_date','visa_end_date','address');
    public function __construct($properties=null){
        parent::$className = __CLASS__;     //a must if you are using static querying methods Application::_count(), Application::getById()
        parent::__construct($properties);
    }

    public function createDetailApplication($data=null) {
        $cnt = count($data['schools']);
        for($i=0; $i<$cnt; $i++) {
            if (strlen(trim($data['schools'][$i])) == 0) continue;
            $a = new CourseApplication();
            $a->school = $data['schools'][$i];
            $a->subject = $data['subjects'][$i];
            $a->course = $data['courses'][$i];
            $a->application_id = $this->id;
            $a->insert();
        }
    }

    public function getCourses() {
        $app = new CourseApplication();
        $apps = $app->get_by_application_id($this->id);
        return $apps;
    }

}
?>
