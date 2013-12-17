<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 12/16/13
 * Time: 11:36 PM
 */
date_default_timezone_set("Europe/Copenhagen");


require dirname(__FILE__)."/../AutoLoader.php";

$dir = dirname(__FILE__)."/../";
AutoLoader::registerDirectory($dir."lib/classes");
AutoLoader::registerDirectory($dir."lib/interfaces");
AutoLoader::registerDirectory($dir."lib/traits");
AutoLoader::registerDirectory($dir."lib/helpers");
AutoLoader::registerDirectory($dir."lib/exceptions");
AutoLoader::registerDirectory(dirname(__FILE__)."/stubs");
AutoLoader::registerDirectory(dirname(__FILE__)."/");

require dirname(__FILE__) . "/../vendor/autoload.php";



