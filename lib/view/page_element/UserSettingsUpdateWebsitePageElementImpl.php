<?php
namespace ChristianBudde\Part\view\page_element;

use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\util\traits\DateTimeTrait;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 9/9/13
 * Time: 5:44 PM
 * To change this template use File | Settings | File Templates.
 * @deprecated
 */
class UserSettingsUpdateWebsitePageElementImpl extends PageElementImpl
{

    use DateTimeTrait;

    /** @var  BackendSingletonContainer */
    private $container;
    /** @var  \ChristianBudde\Part\model\updater\Updater */
    private $updater;

    public function __construct(BackendSingletonContainer $container)
    {
        $this->container = $container;
        $this->updater = $container->getUpdaterInstance();
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

        $checked = $this->updater->isCheckOnLoginAllowed($this->container->getUserLibraryInstance()->getUserLoggedIn())?"checked":"";

        $return = "

        <p class='text update_site'>
        Hjemmesiden er version <span class='version'>{$this->updater->getVersion()}</span> fra <span class='update_time'>{$this->dateString($this->updater->lastUpdated())}</span>.<br />
        Da din hjemmeside understøtter opdateringer, vil du modtage opdateringer i takt med at de bliver udgivet. Denne service er en del af din hosting aftale, og du kan checke efter opdateringer ved at klikke på knappen herunder.<br />
        Der er sidst checket efter opdateringer <span class='check_time'>{$this->dateString($this->updater->lastChecked())}</span>.
        </p>
        <div class='update_site_container container_box'>
                <button class='update_check'
                    data-work-check-value='Undersøger'
                    data-work-update-value='Opdaterer'
                    data-update-value='$msg1'
                    data-check-value='$msg2'>$msg3</button>
        </div>
        <p class='text'>
        Som udgangspunkt checker systemet efter nye opdateringer når du logger på. Dette er anbefalet, da du dermed er sikret den seneste version af din hjemmeside.
        Du kan ændre dette herunder.
        </p>
        <form class='container_box no_loader'>
        <input type='checkbox' $checked class='on_off_checkbox' id='UserSettingsUpdaterEnableAutoUpdate'/>
        <label for='UserSettingsUpdaterEnableAutoUpdate' class='fake_button'>
        </label>
        </form>
        ";


        return $return;
    }

}