<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 7/16/12
 * Time: 7:40 PM
 */
interface Site
{

    const FILE_TRANSFER_PROTOCOL_FTP = 'ftp';
    const FILE_TRANSFER_PROTOCOL_SFTP = 'sftp';

    const EVENT_TITLE_UPDATE = 1;
    const EVENT_DELETE = 2;

    /**
     * @abstract
     * @return PageOrder | bool Will return PageOrder on success, else false if connection is not valid
     */
    public function getPageOrder();

    /**
     * @return Folder | bool Will return the filesystem on success, else false if connection is not valid
     */
    public function getFolder();

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
     * @param string $address
     * @return void
     */
    public function setAddress($address);

    /**
     * @return string
     */
    public function getAddress();

    /**
     * @abstract
     * Will set the DB host of the site
     * @param string $host
     * @return void
     */
    public function setDBHost($host);

    /**
     * @abstract
     * Will set the database of the site
     * @param string $database
     * @return void
     */
    public function setDBDatabase($database);

    /**
     * @abstract
     * Will set the DB user of the site
     * @param string $user
     * @return void
     */
    public function setDBUser($user);


    /**
     * @abstract
     * Will set the DB password of the site
     * @param string $password
     * @return void
     */
    public function setDBPassword($password);


    /**
     * @abstract
     * Will return the DB host of the site
     * @return string
     */
    public function getDBHost();

    /**
     * @abstract
     * Will return the database of the site
     * @return string
     */
    public function getDBDatabase();

    /**
     * @abstract
     * Will return the DB user of the site
     * @return string
     */
    public function getDBUser();


    /**
     * @abstract
     * Will return the DB password of the site
     * @return string
     */
    public function getDBPassword();

    /**
     * @return string
     */
    public function getFTUser();

    /**
     * Will set the File Transfer user
     * @param string $ft_user
     * @return void
     */
    public function setFTUser($ft_user);

    /**
     * @return string
     */
    public function getFTPassword();

    /**
     * Will set the File Transfer password
     * @param $FT_password
     * @return string
     */
    public function setFTPassword($FT_password);

    /**
     * @return string
     */
    public function getFTHost();

    /**
     * Will set the File Transfer host
     * @param string $FT_host
     * @return void
     */
    public function setFTHost($FT_host);

    /**
     * @return string
     */
    public function getFTPort();

    /**
     * Will set the File Transfer port
     * @param string $FT_port
     * @return void
     */
    public function setFTPort($FT_port);

    /**
     * @return string
     */
    public function getFTType();

    /**
     * Will set the File Transfer type (either FTP or SFTP)
     * @param string $FT_type
     * @return void
     */
    public function setFTType($FT_type);
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
