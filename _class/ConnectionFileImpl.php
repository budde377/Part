<?php
require_once dirname(__FILE__).'/../_interface/File.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/28/12
 * Time: 11:54 AM
 * To change this template use File | Settings | File Templates.
 */
class ConnectionFileImpl implements File
{
    public function __construct($path,Connection $connection){

    }

    /**
     * Will return true if the file exists, else false
     * @return bool
     */
    public function exists()
    {
        // TODO: Implement fileExists() method.
    }

    /**
     * Will return a string with the file content or FALSE on failure.
     * If the file does not exists it will return empty string
     * @return string | bool
     */
    public function getContents()
    {
        // TODO: Implement getContents() method.
    }

    /**
     * Get the relative path to some dir.
     *
     * @param string $dirName
     * @return string
     */
    public function getRelativeFilePathTo($dirName)
    {
        // TODO: Implement getRelativeFilePathTo() method.
    }

    /**
     * Return the absolute path to the file.
     * @return string
     */
    public function getAbsoluteFilePath()
    {
        // TODO: Implement getAbsoluteFilePath() method.
    }

    /**
     * Will return the file name as a string
     * @return string
     */
    public function getFileName()
    {
        // TODO: Implement getFileName() method.
    }

    /**
     * Will move the file to specified path
     * @param string $path
     * @return bool TRUE if success FALSE if failure
     */
    public function move($path)
    {
        // TODO: Implement move() method.
    }

    /**
     * @param $path
     * @return null | File Will return null on failure, else File being the new file
     */
    public function copy($path)
    {
        // TODO: Implement copy() method.
    }

    /**
     * Will delete the file
     * @return bool TRUE if success FALSE if failure
     */
    public function delete()
    {
        // TODO: Implement delete() method.
    }

    /**
     * Writes to file
     * @param $string
     * @return int | bool Returns the number of bytes written, or FALSE on error.
     */
    public function write($string)
    {
        // TODO: Implement write() method.
    }

    /**
     * Sets the access mode, available options is in FileModeEnum
     * @param string $permissions
     * @return void
     */
    public function setAccessMode($permissions)
    {
        // TODO: Implement setAccessMode() method.
    }

    /**
     * Gets the current accessMode
     * @return string
     */
    public function getAccessMode()
    {
        // TODO: Implement getAccessMode() method.
    }

    /**
     * Gets the file size in bytes
     * @return int
     */
    public function size()
    {
        // TODO: Implement size() method.
    }

    /**
     * @return Folder Will return parent folder
     */
    public function getParentFolder()
    {
        // TODO: Implement getParentFolder() method.
    }

    /**
     * @return resource | bool Returns a file pointer resource on success, or FALSE on error.
     */
    public function getResource()
    {
        // TODO: Implement getResource() method.
    }
}
