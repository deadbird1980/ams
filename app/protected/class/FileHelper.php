<?php
class FileHelper extends Helper {

    public function getFilesForApplication($app) {
        $files = $app->filesEssential();
        $html = "<select name='application_file[]' required>";
        foreach($files as $file) {
            $required = $file->mandatory ? '*' : '';
            $html .= "<option value={$file->id}>$required{$file->name}</option>";
        }
        $html .= "</select>";
        return $html;
    }

    public function getFileNames($app) {
        $files = $app->filesEssential();
        $required = array();
        foreach($files as $file) {
            if ($file->mandatory) {
                $required[] = "{$file->name}(*)";
            } else {
                $required[] = "{$file->name}";
            }
        }
        return $required;
    }
}
?>
