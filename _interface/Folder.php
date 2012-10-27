<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 13/09/12
 * Time: 22:06
 */
interface Folder extends Iterator
{
    /**
     * @return array | bool Will return an array containing Folders and Files or FALSE on failure
     */
    public function listFolder();

    /**
     * @return bool Return TRUE if delete was successfully else FALSE
     */
    public function delete();

    /**
     * @return bool Return TRUE if folder exists else FALSE
     */
    public function exists();

    /**
     * @return bool Return TRUE on success else FALSE
     */
    public function create();

    /**
     * @param string $path The path to the new file
     * @return bool Return TRUE on success else FALSE
     */
    public function move($path);

    /**
     * @param string $path The path to the new file
     * @return null | File Return null on failure else an instance of File being the new file
     */
    public function copy($path);

    /**
     * @return string The name of the folder
     */
    public function getName();

    /**
     * @return string Will return the absolute path to the folder
     */
    public function getAbsolutePath();

    /**
     * @param string $dirName
     * @return string The relative path to provided dirName
     */
    public function getRelativePathTo($dirName);


    /**
     * @return Folder | null Will return Folder if parent folder exists else null
     */
    public function getParentFolder();
}
