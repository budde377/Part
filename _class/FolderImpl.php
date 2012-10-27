<?php
require_once dirname(__FILE__).'/../_interface/Folder.php';
require_once dirname(__FILE__).'/../_trait/FilePathTrait.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/27/12
 * Time: 1:37 PM
 * To change this template use File | Settings | File Templates.
 */
class FolderImpl implements Folder
{
    use FilePathTrait;
    private $folderPath;

    /**
     * @param string $path Path to folder
     */
    public function __construct($path){
        $this->folderPath = $this->relativeToAbsolute($path);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        // TODO: Implement current() method.
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        // TODO: Implement next() method.
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        // TODO: Implement key() method.
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        // TODO: Implement valid() method.
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        // TODO: Implement rewind() method.
    }

    /**
     * @return array | bool Will return an array containing Folders and Files or FALSE on failure
     */
    public function listFolder()
    {
        $dir = @scandir($this->folderPath);
        if($dir === false){
            return false;
        }
        $resultArray = array();
        foreach($dir as $e){
            if($e != '.' && $e != '..'){
                if(is_dir($this->folderPath.'/'.$e)){
                    $resultArray[] = new FolderImpl($this->folderPath.'/'.$e);
                } else {
                    $resultArray[] = new FileImpl($this->folderPath.'/'.$e);
                }
            }
        }
        return $resultArray;
    }

    /**
     * @return bool Return TRUE if delete was successfully else FALSE
     */
    public function delete()
    {
        return @rmdir($this->folderPath);
    }

    /**
     * @return bool Return TRUE if folder exists else FALSE
     */
    public function exists()
    {
        return file_exists($this->folderPath) && is_dir($this->folderPath);

    }

    /**
     * @return bool Return TRUE on success else FALSE
     */
    public function create()
    {
        return @mkdir($this->folderPath);
    }

    /**
     * @param string $path The path to the new file
     * @return bool Return TRUE on success else FALSE
     */
    public function move($path)
    {
        // TODO: Implement move() method.
    }

    /**
     * @param string $path The path to the new file
     * @return null | File Return null on failure else an instance of File being the new file
     */
    public function copy($path)
    {
        // TODO: Implement copy() method.
    }

    /**
     * @return string The name of the folder
     */
    public function getName()
    {
        $folderArray = explode('/',$this->folderPath);
        return array_pop($folderArray);
    }

    /**
     * @return string Will return the absolute path to the folder
     */
    public function getAbsolutePath()
    {
        return $this->folderPath;
    }

    /**
     * @param string $dirName
     * @return string The relative path to provided dirName
     */
    public function getRelativePathTo($dirName)
    {
        return $this->relativePath($this->folderPath,$dirName);
    }

    /**
     * @return Folder | null Will return Folder if parent folder exists else null
     */
    public function getParentFolder()
    {
        $p = dirname($this->folderPath);
        if($p == $this->folderPath){
            return null;
        }
        return new FolderImpl($p);
    }
}
