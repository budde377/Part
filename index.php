<?php

require_once(dirname(__FILE__) . '/_class/SiteFactoryImpl.php');
require_once(dirname(__FILE__) . '/_class/ConfigImpl.php');
require_once(dirname(__FILE__) . '/_class/WebsiteImpl.php');
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/10/12
 * Time: 11:26 AM
 * To change this template use File | Settings | File Templates.
 */

/** @var $siteConfig SimpleXMLElement */
$siteConfig = simplexml_load_file('SiteConfig.xml');
$config = new ConfigImpl($siteConfig, dirname(__FILE__) . '/');
$factory = new SiteFactoryImpl($config);
$storedEx = null;
$website = new WebsiteImpl($factory);
try {
    $website->generateSite();

} catch (Exception $exception) {
    $storedEx = $exception;

}
unset($website);

if ($storedEx !== null) {
    print $storedEx->getMessage();
}
