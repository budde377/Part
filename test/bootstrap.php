<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 12/16/13
 * Time: 11:36 PM
 */
date_default_timezone_set("Europe/Copenhagen");


require dirname(__FILE__)."/../AutoLoader.php";

AutoLoader::registerAutoloader();
AutoLoader::registerDirectory(dirname(__FILE__)."/../lib");
AutoLoader::registerDirectory(dirname(__FILE__)."/lib");
AutoLoader::registerDirectory(dirname(__FILE__)."/");

$f = dirname(__FILE__) . "/../vendor/autoload.php";
if(file_exists($f)){
    require $f;
}



