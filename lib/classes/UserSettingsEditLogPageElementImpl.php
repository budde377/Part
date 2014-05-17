<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 5/17/14
 * Time: 6:36 PM
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

        $log = $this->container->getLogInstance();
       // $log->log(uniqid("MSG"),pow(2,rand(0,3)), rand(0,1));
        $rows = "";
        $numError = $numWarning = $numNotice = $numDebug = 0;
        foreach($l = $log->listLog() as $entry){
            /** @var $entry array */
            $t = $entry['time'];
            $dumpFile = isset($entry["dumpfile"])?"<a href='#'  data-id='$t' >&nbsp;</a>":"";
            $date = date('j-n-Y \k\l. H:i:s',$t);
            $rows = "
            <tr class='{$this->levelToString($entry['level'], true)}' >
                <td class='level' title='{$this->levelToString($entry['level'])}'> </td>
                <td>{$entry['message']}</td>
                <td class='dumpfile'>$dumpFile</td>
                <td class='date'>$date</td>
            </tr>".$rows;
            switch($entry["level"]){
                case LogFile::LOG_LEVEL_ERROR:
                    $numError++;
                    break;
                case LogFile::LOG_LEVEL_WARNING:
                    $numWarning++;
                    break;
                case LogFile::LOG_LEVEL_NOTICE:
                    $numNotice++;
                    break;
                case LogFile::LOG_LEVEL_DEBUG:
                    $numDebug++;
                    break;
            }
        }
        $emptyClass = count($l)?"":"empty";
        $output = "
        <table id='UserSettingsLogTable' class='$emptyClass'>
            $rows
            <tr class='empty_row'><td>Loggen er tom</td></tr>
        </table>
        <p id='LogInfoParagraph'>
        Der er registreret <i>$numError</i> error, <i>$numWarning</i> warning, <i>$numNotice</i> notice og <i>$numDebug</i> debug indgange.
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

        if($level & LogFile::LOG_LEVEL_ERROR){
            return $lowercase?"error":"Error";
        }
        if($level & LogFile::LOG_LEVEL_WARNING){
            return $lowercase?"warning":"Warning";
        }
        if($level & LogFile::LOG_LEVEL_NOTICE){
            return $lowercase?"notice":"Notice";
        }
        if($level & LogFile::LOG_LEVEL_DEBUG){
            return $lowercase?"debug":"Debug";
        }

        return "";

    }


} 