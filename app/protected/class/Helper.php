<?php
class Helper {
    protected $controller;
    protected $dateFormat = 'dd/mm/yyyy';
    protected $dateElements = array();
    protected $dateClass = 'control textbox validate[required,regexp(^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$)]';

    public function __construct($controller) {
        Doo::loadHelper('DooForm');
        Doo::loadHelper('DooUrlBuilder');
        $this->controller = $controller;
    }

    protected function t($str) {
        return $this->controller->t($str);
    }

    public function convertDateFromDB($date) {
        if (strlen($date) == 0 || $date == '0000-00-00') {
            return "";
        }
        return date("d/m/Y", strtotime($date));
    }

    public function convertDateToDB($date) {
        if (strlen($date) == 0) {
            return "";
        }
        return date("Y-m-d", strtotime($date));
    }

    public function formatDate($arr) {
        foreach($this->dateElements as $elm) {
            if (isset($arr[$elm])) {
                $arr[$elm] = $this->convertDateToDB($arr[$elm]);
            }
        }
        return $arr;
    }
}
?>
