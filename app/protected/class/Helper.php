<?php
class Helper {
    protected $controller;

    public function __construct($controller) {
        Doo::loadHelper('DooForm');
        Doo::loadHelper('DooUrlBuilder');
        $this->controller = $controller;
    }

    protected function t($str) {
        return $this->controller->t($str);
    }
}
?>
