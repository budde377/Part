<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 7/16/12
 * Time: 7:40 PM
 */
interface Site
{


    const EVENT_TITLE_UPDATE = 1;
    const EVENT_DELETE = 2;

    /**
     * @abstract
     * @return PageOrder | bool Will return PageOrder on success, else false if connection is not valid
     */
    public function getPageOrder();

    /**
     * @abstract
     * Will return the title of the site.
     * The title must be unique in a way which conforms to the implementation
     * @return string
     */
    public function getTitle();


    /**
     * @abstract
     * Will set the title and will return false if the title is not unique.
     * @param string $title
     * @return bool
     */
    public function setTitle($title);


    /**
     * @abstract
     * Will set the host of the site
     * @param string $host
     * @return void
     */
    public function setHost($host);

    /**
     * @abstract
     * Will set the database of the site
     * @param string $database
     * @return void
     */
    public function setDatabase($database);

    /**
     * @abstract
     * Will set the user of the site
     * @param string $user
     * @return void
     */
    public function setUser($user);


    /**
     * @abstract
     * Will set the password of the site
     * @param string $password
     * @return void
     */
    public function setPassword($password);


    /**
     * @abstract
     * Will return the host of the site
     * @return string
     */
    public function getHost();

    /**
     * @abstract
     * Will return the database of the site
     * @return string
     */
    public function getDatabase();

    /**
     * @abstract
     * Will return the user of the site
     * @return string
     */
    public function getUser();


    /**
     * @abstract
     * Will return the password of the site
     * @return string
     */
    public function getPassword();

    /**
     * @abstract
     * Create the site on persistent storage
     * @return bool Will return TRUE if site has been created on persistent storage, else FALSE
     */
    public function create();

    /**
     * @abstract
     * Checks if the site has been created on persistent storage.
     * @return bool Will return FALSE if site does not exists on persistent storage, else TRUE
     */
    public function exists();

    /**
     * @abstract
     * Will delete site from persistent storage
     * @return bool Will return FALSE on failure, else TRUE
     */
    public function delete();
}
