<?php

//register global/PHP functions to be used with your template files
//You can move this to common.conf.php   $config['TEMPLATE_GLOBAL_TAGS'] = array('isset', 'empty');
//Every public static methods in TemplateTag class (or tag classes from modules) are available in templates without the need to define in TEMPLATE_GLOBAL_TAGS 
Doo::conf()->TEMPLATE_GLOBAL_TAGS = array('count', 'upper', 'tofloat', 'sample_with_args', 'debug', 'url', 'url2', 'function_deny', 'isset', 'empty', 't', 'file_link');

/**
Define as class (optional)

class TemplateTag {
    public static test(){}
    public static test2(){}
}
**/

function upper($str){
    return strtoupper($str);
}

function tofloat($str){
    return sprintf("%.2f", $str);
}

function sample_with_args($str, $prefix){
    return $str .' with args: '. $prefix;
}

function debug($var){
    if(!empty($var)){
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }
}

//This will be called when a function NOT Registered is used in IF or ElseIF statment
function function_deny($var=null){
   echo '<span style="color:#ff0000;">Function denied in IF or ElseIF statement!</span>';
   exit;
}


//Build URL based on route id
function url($id, $param=null, $addRootUrl=false){
    Doo::loadHelper('DooUrlBuilder');
    // param pass in as string with format
    // 'param1=>this_is_my_value, param2=>something_here'

    if($param!=null){
        $param = explode(', ', $param);
        $param2 = null;
        foreach($param as $p){
            $splited = explode('=>', $p);
            $param2[$splited[0]] = $splited[1];
        }
        return DooUrlBuilder::url($id, $param2, $addRootUrl);
    }

    return DooUrlBuilder::url($id, null, $addRootUrl);
}


//Build URL based on controller and method name
function url2($controller, $method, $param=null, $addRootUrl=false){
    Doo::loadHelper('DooUrlBuilder');
    // param pass in as string with format
    // 'param1=>this_is_my_value, param2=>something_here'

    if($param!=null){
        $param = explode(', ', $param);
        $param2 = null;
        foreach($param as $p){
            $splited = explode('=>', $p);
            $param2[$splited[0]] = $splited[1];
        }
        return DooUrlBuilder::url2($controller, $method, $param2, $addRootUrl);
    }

    return DooUrlBuilder::url2($controller, $method, null, $addRootUrl);
}

function t($str){
    return Doo::getTranslator()->translate($str);
}

function file_link($attachments) {
    if (!$attachments) return '';
    $html = "";
    $baseurl = Doo::conf()->APP_URL . 'index.php';
    if (count($attachments) > 0) {
        $attachment = $attachments[0];
        $html .= "<a href='$baseurl/files?file={$attachment->file_name}&course_application_id={$attachment->course_application_id}&id={$attachment->id}&download=1'>{$attachment->file_name}</a><br>";
        if (count($attachments)>1) {
            $html .= "&nbsp;&nbsp<a href='$baseurl/my/courses/{$attachment->course_application_id}/files'>other files....</a>";
        }
    }
    return $html;
}

?>
