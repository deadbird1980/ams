<?php
Doo::loadModel('User');
Doo::loadModel('Application');
class TaskController extends DooCliController {

    public function checkCourse() {
        $app = new Application();
        $apps = $app->needNotify();
        foreach($apps as $app) {
            print "{$app->id}\n";
        }
    }
}
?>
