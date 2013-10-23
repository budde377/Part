<?php
require_once dirname(__FILE__) . '/../_interface/PageElement.php';
require_once dirname(__FILE__) . '/GitUpdaterImpl.php';
require_once dirname(__FILE__) . '/../_trait/DateTimeTrait.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 9/9/13
 * Time: 5:44 PM
 * To change this template use File | Settings | File Templates.
 */

class UserSettingsUpdateWebsitePageElementImpl implements PageElement
{

    use DateTimeTrait;

    /** @var  BackendSingletonContainer */
    private $container;
    /** @var  Updater */
    private $updater;

    public function __construct(BackendSingletonContainer $container)
    {
        $this->container = $container;
        $this->updater = $container->getUpdater();
    }

    private function dateString($timestamp){

        $timeArr = getdate($timestamp);
        $now = getdate(time());
        $returnString = "";
        if($now['yday'] != $timeArr['yday'] || $timeArr['mday'] != $now['mday']){
            $returnString = "{$this->dayNumberToName($timeArr['wday'])} ";
        } else {
            $returnString = "i dag ";
        }
        if(strtotime("{$now['year']}-{$now['month']}-{$now['mday']}")-strtotime("{$timeArr['year']}-{$timeArr['month']}-{$timeArr['mday']}") > 60*24*7){
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

        $return = "

        <p class='text update_site'>
        Hjemmesiden er version <span class='version'>{$this->updater->getVersion()}</span> fra <span class='update_time'>{$this->dateString($this->updater->lastUpdated())}</span>.<br />
        Da din hjemmeside understøtter opdateringer, vil du modtage opdateringer i takt med at de bliver udgivet. Denne service er en del af din hosting aftale, og du kan checke efter opdateringer ved at klikke på kanppen herunder.<br />
        Der er sidst checket efter opdateringer <span class='check_time'>{$this->dateString($this->updater->lastChecked())}</span>.
        </p>
        <div class='update_site_container'>
                <button class='update_check'>Check for opdateringer</button>
        </div>
        ";


        return $return;
    }
}