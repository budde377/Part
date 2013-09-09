<?php
require_once dirname(__FILE__).'/../_interface/Updater.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/05/13
 * Time: 21:51
 * To change this template use File | Settings | File Templates.
 */

class GitUpdaterImpl implements Updater{

    private $path;

    private $updaters = array();

    public function __construct($path){
        $this->path = $path;

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

        $this->exec("git fetch");
        return $this->getRevision('HEAD') != $this->getRevision("origin/".$this->currentBranch()) ||  array_reduce($this->updaters, function($result, Updater $input){return $result || $input->checkForUpdates();}, false);
    }

    /**
     * Will update the system.
     * As checkForUpdates() this must also be blocking.
     * @return void
     */
    public function update()
    {
        $this->exec('git pull');
		$this->exec('git submodule sync');
		$this->exec('git submodule update');
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

        return array_reduce($this->updaters, function(&$result, Updater $item){$result.= "-".$item->getVersion(); return $result;}, $this->getRevision('HEAD'));
    }

    private function currentBranch(){
        return $this->exec("git branch | sed -n '/\\* /s///p'");
    }

    private function getRevision($where){
        return $this->exec("cd $this->path && git rev-parse --short $where");
    }

    private function listSubmodules(){
        exec("cd $this->path && grep path .gitmodules | sed 's/.*= //'", $modulesList);
        return $modulesList;

    }

    private function exec($command){
        return trim(exec($command));
    }
}