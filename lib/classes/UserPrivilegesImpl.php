<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 18/01/13
 * Time: 22:29
 */
class UserPrivilegesImpl implements UserPrivileges
{
    private $connection;
    /** @var User */
    private $user;
    private $rootPrivilege = 0;
    private $sitePrivilege = 0;
    private $pagePrivilege = array();
    private $addSitePrivilegeStatement;
    private $addRootPrivilegeStatement;
    private $addPagePrivilegeStatement;
    private $valuesHasBeenSet;
    private $revokeRootStatement;
    private $revokeSiteStatement;
    private $revokePageStatement;
    private $revokeAllStatement;

    function __construct(User $user, DB $database)
    {
        $this->user = $user;
        $this->connection = $database->getConnection();
    }


    /**
     * Will add root privileges
     * @return void
     */
    public function addRootPrivileges()
    {
        if($this->addRootPrivilegeStatement == null){
            $this->addRootPrivilegeStatement = $this->connection->prepare("
              INSERT INTO UserPrivileges (username, rootPrivileges, sitePrivileges, pageId) VALUES (?,1,0,NULL)");
        }
        $this->addRootPrivilegeStatement->execute(array($this->user->getUsername()));
        $this->rootPrivilege = 1;
    }

    /**
     * Will add Site privileges
     * @return void
     */
    public function addSitePrivileges()
    {
        if($this->addSitePrivilegeStatement == null){
            $this->addSitePrivilegeStatement = $this->connection->prepare("
              INSERT INTO UserPrivileges (username, rootPrivileges, sitePrivileges, pageId) VALUES (?,0,1,NULL)");
        }
        $this->addSitePrivilegeStatement->execute(array($this->user->getUsername()));
        $this->sitePrivilege = 1;
    }

    /**
     * Will add privileges to given page
     * @param Page $page
     * @return void
     */
    public function addPagePrivileges(Page $page)
    {
        if($this->addPagePrivilegeStatement == null){
            $this->addPagePrivilegeStatement = $this->connection->prepare("
              INSERT INTO UserPrivileges (username, rootPrivileges, sitePrivileges, pageId) VALUES (?,0,0,?)");
        }
        $success = true;
        try{
            $this->addPagePrivilegeStatement->execute(array($this->user->getUsername(),$page->getID()));
        } catch(PDOException $e){
            $success = false;
        }
        if($success){
            $this->pagePrivilege[$page->getID()] = 1;
        }
    }

    /**
     * @return bool Return TRUE if has privilege else FALSE
     */
    public function hasRootPrivileges()
    {
        $this->initialize();
        return $this->rootPrivilege == 1;
    }

    /**
     * @return bool Return TRUE if has privilege else FALSE
     */
    public function hasSitePrivileges()
    {
        $this->initialize();
        return $this->sitePrivilege == 1 || $this->hasRootPrivileges();
    }

    /**
     * @param Page $page
     * @return bool Return TRUE if has privilege else FALSE
     */
    public function hasPagePrivileges(Page $page)
    {
        $this->initialize();
        return $this->hasRootPrivileges() || $this->hasSitePrivileges() || isset($this->pagePrivilege[$page->getID()]);
    }

    /**
     * Will revoke Root privileges
     * @return void
     */
    public function revokeRootPrivileges()
    {
        if($this->revokeRootStatement == null){
            $this->revokeRootStatement =
                $this->connection->prepare("DELETE FROM UserPrivileges WHERE username = ? AND rootPrivileges = 1");
            $this->revokeRootStatement->bindParam(1,$this->user->getUsername());
        }
        $this->revokeRootStatement->execute();
        $this->rootPrivilege = false;
    }

    /**
     * Will revoke Site privileges
     * @return void
     */
    public function revokeSitePrivileges()
    {
        if($this->revokeSiteStatement == null){
            $this->revokeSiteStatement =
                $this->connection->prepare("DELETE FROM UserPrivileges WHERE username = ? AND sitePrivileges = 1");
            $this->revokeSiteStatement->bindParam(1,$this->user->getUsername());
        }
        $this->revokeSiteStatement->execute();
        $this->sitePrivilege = false;
    }

    /**
     * Will revoke privileges from given Page
     * @param Page $page
     * @return void
     */
    public function revokePagePrivileges(Page $page)
    {
        if($this->revokePageStatement == null){
            $this->revokePageStatement =
                $this->connection->prepare("DELETE FROM UserPrivileges WHERE username = ? AND pageId = ?");
        }
        $this->revokePageStatement->execute(array($this->user->getUsername(),$page->getID()));
        unset($this->pagePrivilege[$page->getID()]);
    }

    /**
     * This will revoke all privileges
     * @return void
     */
    public function revokeAllPrivileges()
    {
        if($this->revokeAllStatement == null){
            $this->revokeAllStatement =
                $this->connection->prepare("DELETE FROM UserPrivileges WHERE username = ?");
            $this->revokeAllStatement->bindParam(1,$this->user->getUsername());
        }
        $this->revokeAllStatement->execute();
        $this->rootPrivilege = $this->sitePrivilege = 0;
        $this->pagePrivilege = array();
    }

    private function initialize()
    {
        if(!$this->valuesHasBeenSet){
            $stm = $this->connection->prepare("SELECT * FROM UserPrivileges WHERE username = ?");
            $stm->execute(array($this->user->getUsername()));
            foreach($stm->fetchAll(PDO::FETCH_ASSOC) as $row){
                $this->rootPrivilege = $this->rootPrivilege || $row['rootPrivileges'] == 1;
                $this->sitePrivilege = $this->sitePrivilege || $row['sitePrivileges'] == 1;
                if(($p = $row['pageId']) != null){
                    $this->pagePrivilege[$p] = 1;
                }
            }
            $this->valuesHasBeenSet = true;
        }
    }


    /**
     * Will return an array of strings containing the sites that are under the users control.
     * If the user has site or root privileges an empty array is returned.
     * If the user has no privileges an empty array is returned.
     *
     * @param PageOrder $pageOrder If order is given it will return array containing instances from the PageOrder
     * @return array
     */
    public function listPagePrivileges(PageOrder $pageOrder = null)
    {
        $this->initialize();
        if($this->hasRootPrivileges() || $this->hasSitePrivileges()){
            return array();
        }
        $returnArray = array();

        foreach($this->pagePrivilege as $key=>$val){
            if($pageOrder instanceof PageOrder){
                $returnArray[] = $pageOrder->getPage($key);
            } else {
                $returnArray[] = $key;
            }
        }
        return $returnArray;
    }
}
