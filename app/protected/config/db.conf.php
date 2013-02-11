<?php
/**
 * Example Database connection settings and DB relationship mapping
 * $dbmap[Table A]['has_one'][Table B] = array('foreign_key'=> Table B's column that links to Table A );
 * $dbmap[Table B]['belongs_to'][Table A] = array('foreign_key'=> Table A's column where Table B links to );
 **/

//User relationship
$dbmap['User']['has_many']['Application'] = array('foreign_key'=>'user_id');


//Application relationship
$dbmap['Application']['has_many']['Attachment'] = array('foreign_key'=>'application_id');
$dbmap['Application']['belongs_to']['User'] = array('foreign_key'=>'id');
$dbmap['Application']['belongs_to']['Assignee'] = array('foreign_key'=>'id');
$dbmap['Assignee']['has_many']['Application'] = array('foreign_key'=>'assignee_id');
//$dbmap['Food']['has_many']['Ingredient'] = array('foreign_key'=>'food_id', 'through'=>'food_has_ingredient');
$dbmap['Attachment']['belongs_to']['Application'] = array('foreign_key'=>'id');

$dbconfig['dev'] = array('localhost', 'ams_dev', 'root', '', 'mysql',true);
if (isset($_ENV['CLEARDB_DATABASE_URL'])) {
    $url = parse_url($_ENV["CLEARDB_DATABASE_URL"]);
    $dbconfig['dev'] = array($url['host'], trim($url['path'],'/'), $url['user'], $url['pass'], $url['scheme'], true);
}
?>
