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
$siteConfig = simplexml_load_file('../site-config.xml');
$config = new ChristianBudde\cbweb\ConfigImpl($siteConfig, '../');

$factory = isset($factory) ? $factory : new ChristianBudde\cbweb\SiteFactoryImpl($config);

$setUp = function () use ($factory) {
    $website = new ChristianBudde\cbweb\WebsiteImpl($factory);
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
        $mail = new ChristianBudde\cbweb\MailImpl();
        $backendContainer = $factory->buildBackendSingletonContainer($config);

        foreach ($backendContainer->getUserLibraryInstance() as $user) {
            /** @var $user ChristianBudde\cbweb\User */
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
        $mail->setMailType(ChristianBudde\cbweb\Mail::MAIL_TYPE_HTML);
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
            ChristianBudde\cbweb\HTTPHeaderHelper::redirectToLocation("/_500");
        }


    }
}

unset($website);