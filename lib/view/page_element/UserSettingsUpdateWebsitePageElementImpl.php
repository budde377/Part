<?php
namespace ChristianBudde\cbweb\view\page_element;
use ChristianBudde\cbweb\model\updater\Updater;

use ChristianBudde\cbweb\BackendSingletonContainer;
use ChristianBudde\cbweb\util\traits\DateTimeTrait;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 9/9/13
 * Time: 5:44 PM
 * To change this template use File | Settings | File Templates.
 */
class UserSettingsUpdateWebsitePageElementImpl extends PageElementImpl
{

    use DateTimeTrait;

    /** @var  BackendSingletonContainer */
    private $container;
    /** @var  \ChristianBudde\cbweb\model\updater\Updater */
    private $updater;

    public function __construct(BackendSingletonContainer $container)
    {
        $this->container = $container;
        $this->updater = $container->getUpdater();
    }

    private function dateString($timestamp)
    {

        $timeArr = getdate($timestamp);
        $now = getdate(time());
        if ($now['yday'] != $timeArr['yday'] || $timeArr['mday'] != $now['mday']) {
            $returnString = "{$this->dayNumberToName($timeArr['wday'])} ";
        } else {
            $returnString = "i dag ";
        }
        if (strtotime("{$now['year']}-{$now['month']}-{$now['mday']}") - strtotime("{$timeArr['year']}-{$timeArr['month']}-{$timeArr['mday']}") > 60 * 24 * 7) {
            $returnString .= "d. {$timeArr['mday']}. {$this->monthNumberToName($timeArr['mon'])} {$timeArr['year']} ";
        }

        $returnString .= "kl. {$this->addLeadingZero($timeArr['hours'])}:{$this->addLeadingZero($timeArr['minutes'])}";

        return trim($returnString);
    }


    /**
     * This will return content from page element as a string.
     * The format can be xml, xhtml, html etc. but return type must be string
     * @return string
     */
    public function generateContent()
    {
        parent::generateContent();

        $msg1 = 'Opdater hjemmesiden';
        $msg2 = 'Check for opdateringer';
        $msg3 = $this->updater->checkForUpdates(true)?$msg1:$msg2;

        $return = "

        <p class='text update_site'>
        Hjemmesiden er version <span class='version'>{$this->updater->getVersion()}</span> fra <span class='update_time'>{$this->dateString($this->updater->lastUpdated())}</span>.<br />
        Da din hjemmeside understøtter opdateringer, vil du modtage opdateringer i takt med at de bliver udgivet. Denne service er en del af din hosting aftale, og du kan checke efter opdateringer ved at klikke på knappen herunder.<br />
        Der er sidst checket efter opdateringer <span class='check_time'>{$this->dateString($this->updater->lastChecked())}</span>.
        </p>
        <div class='update_site_container'>
                <button class='update_check'
                    data-work-check-value='Undersøger'
                    data-work-update-value='Opdaterer'
                    data-update-value='$msg1'
                    data-check-value='$msg2'>$msg3</button>
        </div>
        ";


        return $return;
    }

}