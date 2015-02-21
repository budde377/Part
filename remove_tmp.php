<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/19/15
 * Time: 9:53 AM
 */


use ChristianBudde\Part\util\file\Folder;

@include 'vendor/autoload.php';
@include 'local.php';

if (file_exists('site-config.xml')) {
    $siteConfig = simplexml_load_file('site-config.xml');
} else if (file_exists('site-config.xml.dist')) {
    $siteConfig = simplexml_load_file('site-config.xml.dist');
} else {
    die;
}

$config = new ChristianBudde\Part\ConfigImpl($siteConfig, '../');
$factory = isset($factory) ? $factory : new ChristianBudde\Part\SiteFactoryImpl($config);
$tmpFolder = $factory->buildBackendSingletonContainer($config)->getTmpFolderInstance();
if($tmpFolder == null){
    die;
}
$tmpFolder->delete(Folder::DELETE_FOLDER_RECURSIVE);
