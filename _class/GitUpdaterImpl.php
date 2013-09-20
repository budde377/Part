<?php
require_once dirname(__FILE__).'/../_interface/Updater.php';
require_once dirname(__FILE__).'/../_class/FileImpl.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/05/13
 * Time: 21:51
 * To change this template use File | Settings | File Templates.
 */

class GitUpdaterImpl implements Updater{

    private $path;
    private $branch;
    private $currentVersion;
    private $lastCheckedFile;
    private $lastCheckedTime;
    private $canBeUpdated = false;

    private $updaters = array();

    public function __construct($path){
        $this->path = $path;
        $this->lastCheckedFile = new FileImpl($path.'/.lastCheckForUpdates');
        $this->lastCheckedFile->setAccessMode(File::FILE_MODE_W_TRUNCATE_FILE_TO_ZERO_LENGTH);
        foreach($this->listSubmodules() as $module){
            $this->updaters[$module] = new GitUpdaterImpl($path."/$module");

        }
    }

    /**
     * Will check if there exists a new update.
     * This must be blocking if using external program such as git.
     * @return bool Return TRUE on existing new update, else FALSE
     */
    public function checkForUpdates()
    {
        $this->writeTime();
        if($this->canBeUpdated){
            return true;
        }
        $this->exec("git fetch");
        return $this->canBeUpdated = ($this->getRevision('HEAD') != $this->getRevision("origin/".$this->currentBranch()) ||  array_reduce($this->updaters, function($result, Updater $input){return $result || $input->checkForUpdates();}, false));
    }

    private function writeTime(){
        $this->lastCheckedFile->write($this->lastCheckedTime = time());
        return $this->lastCheckedTime;
    }

    private function readTime(){
        return $this->lastCheckedTime == null?($this->lastCheckedTime = $this->lastCheckedFile->exists()?intval($this->lastCheckedFile->getContents()):$this->lastUpdated()):$this->lastCheckedTime;
    }

    /**
     * Will update the system.
     * As checkForUpdates() this must also be blocking.
     * @return void
     */
    public function update()
    {
        if(!$this->checkForUpdates()){
            return;
        }
        $this->exec('git pull');
        foreach($this->updaters as $updater){
            /** @var $updater Updater */
            $updater->update();
        }
        $this->canBeUpdated = false;
    }

    /**
     * Last update
     * @return int Timestamp of last update
     */
    public function lastUpdated()
    {

        return array_reduce($this->updaters, function ($result, Updater $item) {$result = max($result, $item->lastUpdated()); return $result;}, strtotime($this->exec("git show -s --format=\"%ci\"")));
    }

    /**
     * @return string This should return a string containing some representation for the version for support reference
     */
    public function getVersion()
    {

        return $this->currentVersion == null?$this->currentVersion = array_reduce($this->updaters, function(&$result, Updater $item){$result.= "-".$item->getVersion(); return $result;}, $this->getRevision('HEAD')):$this->currentVersion;
    }

    private function currentBranch(){

        return $this->branch == null?$this->branch = $this->exec("git branch | sed -n '/\\* /s///p'"):$this->branch;
    }

    private function getRevision($where){
        return $this->exec("git rev-parse --short $where");
    }

    private function listSubmodules(){
        exec("cd $this->path && grep path .gitmodules | sed 's/.*= //'", $modulesList);
        return $modulesList;
    }

    private function exec($command){
        $ret = trim(shell_exec("cd $this->path && $command"));
        return $ret;
    }

    /**
     * Will return timestamp of last checked
     * @return int
     */
    public function lastChecked()
    {
        return max($this->readTime(), $this->lastUpdated());
    }
}