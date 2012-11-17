<?php
/**
 * Example Database connection settings and DB relationship mapping
 * $dbmap[Table A]['has_one'][Table B] = array('foreign_key'=> Table B's column that links to Table A );
 * $dbmap[Table B]['belongs_to'][Table A] = array('foreign_key'=> Table A's column where Table B links to );

//Food relationship
$dbmap['Food']['belongs_to']['FoodType'] = array('foreign_key'=>'id');
$dbmap['Food']['has_many']['Article'] = array('foreign_key'=>'food_id');
$dbmap['Food']['has_one']['Recipe'] = array('foreign_key'=>'food_id');
$dbmap['Food']['has_many']['Ingredient'] = array('foreign_key'=>'food_id', 'through'=>'food_has_ingredient');
*/

//$dbconfig[ Environment or connection name] = array(Host, Database, User, Password, DB Driver, Make Persistent Connection?);
$dbconfig['dev'] = array('localhost', 'ams_dev', 'root', '', 'mysql',true);
// $dbconfig['prod'] = array('localhost', 'database', 'root', '1234', 'mysql',true);
?>
