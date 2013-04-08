<?php
class FileHelper extends Helper {

    public function getFilesForApplication($app) {
        $files = $app->applicationFiles();
        $html = "<select name='application_file[]' required>";
        foreach($files as $file) {
            $required = $file->mandatory ? '*' : '';
            $html .= "<option value={$file->id}>$required{$file->name}</option>";
        }
        $html .= "</select>";
        return $html;
    }

    public function getFileNames($app) {
        $files = $app->applicationFiles();
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

    public function getFileJSON($app) {
        $afs = $app->applicationFiles();
        $files = array();
        foreach($afs as $af) {
            $file = new StdClass();
            $file->id = $af->id;
            $file->mandatory = $af->mandatory;
            $files[] = $file;
        }
        return json_encode($files);
    }

}
?>
