<?php
/**
 * Example Database connection settings and DB relationship mapping
 * $dbmap[Table A]['has_one'][Table B] = array('foreign_key'=> Table B's column that links to Table A );
 * $dbmap[Table B]['belongs_to'][Table A] = array('foreign_key'=> Table A's column where Table B links to );
 **/

//User relationship
$dbmap['Application']['belongs_to']['User'] = array('foreign_key'=>'id');
$dbmap['User']['has_many']['Application'] = array('foreign_key'=>'user_id');
//$dbmap['Food']['has_many']['Ingredient'] = array('foreign_key'=>'food_id', 'through'=>'food_has_ingredient');

$dbconfig['dev'] = array('localhost', 'ams_dev', 'root', '', 'mysql',true);
?>
