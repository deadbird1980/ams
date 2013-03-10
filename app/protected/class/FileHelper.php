<?php
class FileHelper extends Helper {

    public function getFilesForApplication($app) {
        $files = $app->files();
        $html = "<select name='application_file[]'>";
        foreach($files as $file) {
            $html .= "<option value={$file->id}>{$file->name}</option>";
        }
        $html .= "</select>";
        return $html;
    }
}
?>
