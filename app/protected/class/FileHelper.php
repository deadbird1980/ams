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
}
?>
