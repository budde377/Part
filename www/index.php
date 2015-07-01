<?php

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/10/12
 * Time: 11:26 AM
 * To change this template use File | Settings | File Templates.
 */
session_start();

// LOAD COMPOSER
@include '../vendor/autoload.php';
// PROVIDE A WAY TO INITIALIZE SITE FACTORY
@include '../local.php';

date_default_timezone_set("Europe/Copenhagen");
/** @var $siteConfig SimpleXMLElement */

if(file_exists('../site-config.xml')){
    $siteConfig = simplexml_load_file('../site-config.xml');
} else if (file_exists('../site-config.xml.dist')){
    $siteConfig = simplexml_load_file('../site-config.xml.dist');
} else{
    die;
}

$config = new ChristianBudde\Part\ConfigImpl($siteConfig, '../');
$cachePath = $config->getTmpFolderPath().'/cache/siteFactory';
if($config->isCacheEnabled() && file_exists($cachePath)){
    $factory = unserialize(file_get_contents($cachePath));
}

$factory = isset($factory) ? $factory : new ChristianBudde\Part\SiteFactoryImpl($config);

$setUp = function () use ($factory) {
    $website = new ChristianBudde\Part\WebsiteImpl($factory);
    $website->generateSite();
    return $website;
};

if ($config->isDebugMode()) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $setUp();
} else {
    try {
        $setUp();
    } catch (Exception $exception) {
        ob_clean();
        $mail = new \ChristianBudde\Part\util\mail\MailImpl();
        $backendContainer = $factory->buildBackendSingletonContainer($config);

        foreach ($backendContainer->getUserLibraryInstance() as $user) {
            /** @var $user \ChristianBudde\Part\model\user\User */
            if ($user->getUserPrivileges()->hasRootPrivileges()) {
                $mail->addReceiver($user);
            }
        }
        $printVars = function ($title, $var) {
            $var = str_replace("\n", "<br />", print_r($var, true));
            return "        <u><b>$title</b></u><br />
                    $var<br />";
        };

        $message = "Hej<br />
        Du modtager denne mail fordi der er sket en fejl på en af de sider, som du er <i>root</i> bruger på.";

        $mail->setMessage($message);
        $host = $_SERVER['HTTP_HOST'];

        $mail->setSubject("Fejl på $host");
        $mail->setSender("no-reply@$host");
        $mail->setMailType(\ChristianBudde\Part\util\mail\Mail::MAIL_TYPE_HTML);
        $mail->sendMail();

        if ($log = $backendContainer->getLoggerInstance()) {
            $d = $log->error("PHP Exception", [
                "Exception" => $exception,
                '$_SERVER' => $_SERVER,
                '$_POST' => $_POST,
                '$_GET' => $_GET,
                '$_SESSION' => $_SESSION,
                '$_COOKIE' => $_COOKIE
            ]);

        }

        if (!isset($_SERVER['REQUEST_URI']) || strpos($_SERVER['REQUEST_URI'], '_500') === false) {
            \ChristianBudde\Part\util\helper\HTTPHeaderHelper::redirectToLocation("/_500");
        }


    }
}

unset($website);

if($config->isCacheEnabled()){
    if(!file_exists($dir = dirname($cachePath))){
        mkdir($dir, 0777, true);
    }
    file_put_contents($cachePath, serialize($factory));
}