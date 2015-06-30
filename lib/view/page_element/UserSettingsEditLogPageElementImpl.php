<?php
namespace ChristianBudde\Part\view\page_element;
use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\log\Logger;
use ChristianBudde\Part\util\traits\DateTimeTrait;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/17/14
 * Time: 6:36 PM
 * @deprecated
 */

class UserSettingsEditLogPageElementImpl extends PageElementImpl{

    use DateTimeTrait;

    private $container;

    function __construct(BackendSingletonContainer $container)
    {
        $this->container = $container;
    }

    public function generateContent()
    {
        parent::generateContent();

        $log = $this->container->getLoggerInstance();
        $rows = "";
        foreach($l = $log->listLog() as $entry){
            /** @var $entry array */
            $t = $entry['time'];
            $dumpFile = isset($entry["context"])?"<a href='#'  data-id='$t' >&nbsp;</a>":"";
            $date = date('j-n-Y \k\l. H:i:s',$t);
            $rows = "
            <tr class='{$this->levelToString($entry['level'], true)}' >
                <td class='level' title='{$this->levelToString($entry['level'])}'> </td>
                <td>{$entry['message']}</td>
                <td class='dumpfile'>$dumpFile</td>
                <td class='date'>$date</td>
            </tr>".$rows;

        }
        $emptyClass = count($l)?"":"empty";
        $count = count($l);
        $output = "
        <table id='UserSettingsLogTable' class='$emptyClass'>
            $rows
            <tr class='empty_row'><td>Loggen er tom</td></tr>
        </table>
        <p id='LogInfoParagraph'>
        Der er registreret <i>$count</i> indgange.
        <a href='#' id='ClearLogLink'>Ryd loggen</a>.
        </p>
        ";



        return $output;



    }

    /**
     * @param String $level
     * @param bool $lowercase
     * @return String
     */
    private function levelToString($level, $lowercase = false)
    {
        if(!$lowercase){
            return ucwords($this->levelToString($level, true));
        }

        switch($level){
            case Logger::LOG_LEVEL_ALERT:
                return "alert";
                break;
            case Logger::LOG_LEVEL_DEBUG:
                return "debug";
                break;
            case Logger::LOG_LEVEL_CRITICAL:
                return "critical";
                break;
            case Logger::LOG_LEVEL_EMERGENCY:
                return "emergency";
                break;
            case Logger::LOG_LEVEL_INFO:
                return "info";
                break;
            case Logger::LOG_LEVEL_NOTICE:
                return "notice";
                break;
            case Logger::LOG_LEVEL_WARNING:
                return "warning";
                break;
            case Logger::LOG_LEVEL_ERROR:
                return "error";
                break;
        }


        return "";

    }


} 