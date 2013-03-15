<?php
class FileHelper extends Helper {

    public function getFilesForApplication($app) {
        $files = $app->filesEssential();
        $html = "<select name='application_file[]' required>";
        foreach($files as $file) {
            $html .= "<option value={$file->id}>{$file->name}</option>";
        }
        $html .= "</select>";
        return $html;
    }

    public function getFilesRequired($app) {
        $files = $app->filesEssential();
        $names = array();
        foreach($files as $file) {
            $names[] = "{$file->name}";
        }
        return array('count'=>count($files), 'text'=>join($names, ','));
    }
}
?>
