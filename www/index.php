<?php

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/10/12
 * Time: 11:26 AM
 * To change this template use File | Settings | File Templates.
 */
session_start();

// LOCAL AUTOLOADERS

require dirname(__FILE__)."/../AutoLoader.php";

AutoLoader::registerAutoloader();
AutoLoader::registerDirectory(dirname(__FILE__)."/../lib");
AutoLoader::registerDirectory(dirname(__FILE__)."/../../lib");
// LOAD COMPOSER
require dirname(__FILE__).'/../vendor/autoload.php';
@include dirname(__FILE__).'/../../vendor/autoload.php';

date_default_timezone_set("Europe/Copenhagen");
/** @var $siteConfig SimpleXMLElement */
$siteConfig = simplexml_load_file('../site-config.xml');
$config = new ConfigImpl($siteConfig, dirname(__FILE__) . '/../../');
$factory = new SiteFactoryImpl($config);

$setUp = function() use ($factory){
    $website = new WebsiteImpl($factory);
    $website->generateSite();
    return $website;
};

if($config->isDebugMode()){
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $setUp();
} else {
    try {
        $setUp();
    } catch (Exception $exception) {
        ob_clean();
        $mail = new MailImpl();
        $backendContainer = $factory->buildBackendSingletonContainer($config);
        /** @var $user User */
        foreach($backendContainer->getUserLibraryInstance() as $user){
            if($user->getUserPrivileges()->hasRootPrivileges()){
                $mail->addReceiver($user);
            }
        }
        $printVars = function ($title, $var){
            $var = str_replace("\n","<br />", print_r($var,true));
            return "        <u><b>$title</b></u><br />
                    $var<br />";
        };

        $message = "Hej<br />
        Du modtager denne mail fordi der er sket en fejl på en af de sider, som du er <i>root</i> bruger på.";

        $mail->setMessage($message);
        $host = $_SERVER['HTTP_HOST'];

        $mail->setSubject("Fejl på $host");
        $mail->setSender("no-reply@$host");
        $mail->setMailType(Mail::MAIL_TYPE_HTML);
        $mail->sendMail();

        if($log = $backendContainer->getLogInstance()){
            $d = $log->log("PHP Exception", LogFile::LOG_LEVEL_ERROR, true);
            $d->dumpVar("Exception", $exception);
            $d->dumpVar('$_SERVER', $_SERVER);
            $d->dumpVar('$_POST', $_POST);
            $d->dumpVar('$_GET', $_GET);
            $d->dumpVar('$_SESSION', $_SESSION);
            $d->dumpVar('$_COOKIE', $_COOKIE);
        }



        if(!isset($_SERVER['REQUEST_URI']) || strpos($_SERVER['REQUEST_URI'], '_500') === false){
            HTTPHeaderHelper::redirectToLocation("/_500");
        }


    }
}

unset($website);