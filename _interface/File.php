<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/1/12
 * Time: 10:30 AM
 * To change this template use File | Settings | File Templates.
 */
interface File
{

    const FILE_MODE_R_POINTER_AT_BEGINNING = 'rb';
    const FILE_MODE_RW_POINTER_AT_BEGINNING = 'r+b';
    const FILE_MODE_W_TRUNCATE_FILE_TO_ZERO_LENGTH = 'wb';
    const FILE_MODE_RW_TRUNCATE_FILE_TO_ZERO_LENGTH = 'w+b';
    const FILE_MODE_W_POINTER_AT_END = 'ab';
    const FILE_MODE_RW_POINTER_AT_END = 'a+b';

    /**
     * @abstract
     * Will return true if the file exists, else false
     * @return bool
     */
    public function exists();

    /**
     * @abstract
     * Will return a string with the file content or FALSE on failure.
     * If the file does not exists it will return empty string
     * @return string | bool
     */
    public function getContents();

    /**
     * @abstract
     * Get the relative path to some dir.
     *
     * @param string $dirName
     * @return string
     */
    public function getRelativeFilePathTo($dirName);


    /**
     * @abstract
     * Return the absolute path to the file.
     * @return string
     */
    public function getAbsoluteFilePath();

    /**
     * @abstract
     * Will return the file name as a string
     * @return string
     */
    public function getFileName();


    /**
     * @abstract
     * Will move the file to specified path
     * @param string $path
     * @return bool TRUE if success FALSE if failure
     */
    public function move($path);

    /**
     * @abstract
     * @param $path
     * @return null | File Will return null on failure, else File being the new file
     */
    public function copy($path);


    /**
     * @abstract
     * Will delete the file
     * @return bool TRUE if success FALSE if failure
     */
    public function delete();

    /**
     * @abstract
     * Writes to file
     * @param $string
     * @return int | bool Returns the number of bytes written, or FALSE on error.
     */
    public function write($string);


    /**
     * @abstract
     * Sets the access mode, available options is in FileModeEnum
     * @param string $permissions
     * @return void
     */
    public function setAccessMode($permissions);


    /**
     * @abstract
     * Gets the current accessMode
     * @return string
     */
    public function getAccessMode();


    /**
     * @abstract
     * Gets the file size in bytes
     * @return int
     */
    public function size();


    /**
     * @return Folder Will return parent folder
     */
    public function getParentFolder();

}
