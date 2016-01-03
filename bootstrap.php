<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/20/15
 * Time: 11:11 AM
 */

@include "vendor/autoload.php";
@include 'local.php';

if(file_exists('site-config.xml')){
    $siteConfig = simplexml_load_file('site-config.xml');
} else if (file_exists('site-config.xml.dist')){
    $siteConfig = simplexml_load_file('site-config.xml.dist');
}

if(isset($siteConfig, $GLOBALS['DB_NAME'], $GLOBALS['DB_HOST'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWORD'])){

    $config = new ChristianBudde\Part\ConfigImpl($siteConfig, ".");

    $newConfig = new \ChristianBudde\Part\StubConfigImpl();
    $connection = $config->getMySQLConnection();
    $connection['database'] = $GLOBALS['DB_NAME'];
    $connection['host'] = $GLOBALS['DB_HOST'];
    $connection['user'] = $GLOBALS['DB_USER'];
    $connection['password'] = $GLOBALS['DB_PASSWORD'];
    $newConfig->setMysqlConnection($connection);

    $factory = isset($factory) ? $factory : new ChristianBudde\Part\SiteFactoryImpl($newConfig);
    try{
        $factory->buildBackendSingletonContainer($newConfig)->getDBInstance()->update();

    } catch(PDOException $e){

    }

    unset($siteConfig, $config, $factory, $newConfig, $connection);

}

