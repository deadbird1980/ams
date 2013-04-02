<?php
Doo::loadCore('db/DooSmartModel');

class CourseApplication extends DooSmartModel{

    public $id;
    public $application_id;
    public $school;
    public $subject;
    public $course;
    public $status;
    public $sent;
    public $_table = 'course_application';
    public $_primarykey = 'id';
    public $_fields = array('id','application_id','school','subject','course','status');
    public function __construct($properties=null){
        parent::$className = __CLASS__;     //a must if you are using static querying methods Application::_count(), Application::getById()
        parent::__construct($properties);
    }
}
?>
