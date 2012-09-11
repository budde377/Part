<?php
require_once dirname(__FILE__) . '/../../_interface/Site.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 7/18/12
 * Time: 1:17 AM
 */
class StubSiteImpl implements Site
{

    private $pageOrder;
    private $tile;
    public $host;
    public $database;
    public $user;
    public $password;

    /**
     * @return PageOrder | bool Will return PageOrder on success, else false if connection is not valid
     */
    public function getPageOrder()
    {
        return $this->pageOrder;
    }

    /**
     * Will return the title of the site.
     * The title must be unique in a way which conforms to the implementation
     * @return string
     */
    public function getTitle()
    {
        return $this->tile;
    }

    /**
     * Will set the title and will return false if the title is not unique.
     * @param string $title
     * @return bool
     */
    public function setTitle($title)
    {
        $this->tile = $title;
    }

    /**
     * Will set the host of the site
     * @param string $host
     * @return void
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * Will set the database of the site
     * @param string $database
     * @return void
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }

    /**
     * Will set the user of the site
     * @param string $user
     * @return void
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Will set the password of the site
     * @param string $password
     * @return void
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Will return the host of the site
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Will return the database of the site
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Will return the user of the site
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Will return the password of the site
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Create the site on persistent storage
     * @return bool Will return TRUE if site has been created on persistent storage, else FALSE
     */
    public function create()
    {
        return false;
    }

    /**
     * Checks if the site has been created on persistent storage.
     * @return bool Will return FALSE if site does not exists on persistent storage, else TRUE
     */
    public function exists()
    {
        return true;
    }

    /**
     * Will delete site from persistent storage
     * @return bool Will return FALSE on failure, else TRUE
     */
    public function delete()
    {
        return false;
    }

    public function setPageOrder($pageOrder)
    {
        $this->pageOrder = $pageOrder;
    }
}
