<?php
namespace ChristianBudde\cbweb;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/05/13
 * Time: 21:51
 * To change this template use File | Settings | File Templates.
 */
class GitUpdaterImpl implements Updater
{

    private $path;
    private $site;
    private $subUpdaters = array();
    private $subModuleUpdater;
    private $currentVersion;
    private $canBeUpdated;
    private $branch;

    /**
     * @param string $path
     * @param Site $site
     * @param bool $subModule
     */
    function __construct($path, Site $site, $subModule = false)
    {
        $this->path = $path;
        $this->site = $site;
        $this->subModuleUpdater = $subModule;
        foreach ($this->listSubModules() as $module) {
            $this->subUpdaters[$module] = new GitUpdaterImpl("$path/$module", $site, true);
        }
    }


    /**
     * Will check if there exists a new update.
     * This must be blocking if using external program such as git.
     * @param bool $quick If TRUE will do a quick check. May contain false positives.
     * @return bool Return TRUE on existing new update, else FALSE
     */
    public function checkForUpdates($quick = false)
    {

        if (!$this->subModuleUpdater) {
            $this->site->getVariables()->setValue("last_checked", time());
        }
        if ($this->canBeUpdated === null) {
            $this->canBeUpdated = $this->site->getVariables()->getValue("can_be_updated") == 1;
        }
        if ($this->canBeUpdated) {
            return true;
        }

        if($quick){
            return false;
        }

        $this->exec("git fetch");

        $updateAvailable = ($this->getRevision('HEAD') != $this->getRevision("origin/" . $this->currentBranch()) || array_reduce($this->subUpdaters, function ($result, Updater $input) {
                return $result || $input->checkForUpdates();
            }, false));
        if ($this->canBeUpdated != $updateAvailable) {
            $this->site->getVariables()->setValue("can_be_updated", $updateAvailable ? 1 : 0);
        }

        return $this->canBeUpdated = $updateAvailable;
    }

    /**
     * Will update the system.
     * As checkForUpdates() this must also be blocking.
     * @return void
     */
    public function update()
    {
        if (!$this->checkForUpdates()) {
            return;
        }
        if (!$this->subModuleUpdater) {
            $this->site->getVariables()->setValue("last_updated", time());
        }
        $this->exec("git fetch origin");
        $this->exec("git reset --hard origin/{$this->currentBranch()}");
        /** @var $updater Updater */
        foreach ($this->subUpdaters as $updater) {
            $updater->update();
        }
        $this->canBeUpdated = false;
        $this->site->getVariables()->setValue("can_be_updated", 0);
        $this->site->modify();
        $this->currentVersion = null;

    }

    /**
     * Will return timestamp of last checked
     * @return int
     */
    public function lastChecked()
    {
        return $this->site->getVariables()->getValue('last_checked');
    }

    /**
     * Last update
     * @return int Timestamp of last update
     */
    public function lastUpdated()
    {
        return $this->site->getVariables()->getValue('last_updated');
    }

    /**
     * @return string This should return a string containing some representation for the version for support reference
     */
    public function getVersion()
    {
        if ($this->currentVersion != null) {
            return $this->currentVersion;
        }
        return $this->currentVersion = array_reduce($this->subUpdaters, function (&$result, Updater $item) {
            $result .= "-" . $item->getVersion();
            return $result;
        }, $this->getRevision('HEAD'));
    }

    private function listSubModules()
    {
        exec("cd $this->path && grep -s path .gitmodules | sed 's/.*= //'", $modulesList);
        return $modulesList;
    }

    private function getRevision($where)
    {
        return $this->exec("git rev-parse --short $where");
    }


    private function exec($command)
    {
        $ret = trim(shell_exec("cd $this->path && $command"));
        return $ret;
    }

    private function currentBranch()
    {

        return $this->branch == null ? $this->branch = $this->exec("git branch | sed -n '/\\* /s///p'") : $this->branch;
    }
}