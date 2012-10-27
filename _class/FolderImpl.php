<?php
require_once dirname(__FILE__) . '/../_interface/Folder.php';
require_once dirname(__FILE__) . '/../_trait/FilePathTrait.php';
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
    private $key;
    private $folderList;

    /**
     * @param string $path Path to folder
     */
    public function __construct($path)
    {
        $this->folderPath = $this->relativeToAbsolute($path);
        $this->key = 0;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        if($this->folderList == null){
            $this->folderList = $this->listFolder();
        }
        return $this->folderList[$this->key];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->key++;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->key;
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
        if($this->folderList == null){
            $this->folderList = $this->listFolder();
        }
        return is_array($this->folderList) && isset($this->folderList[$this->key]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->key = 0;
        $this->folderList = $this->listFolder();
    }

    /**
     * @return array | bool Will return an array containing Folders and Files or FALSE on failure
     */
    public function listFolder()
    {
        $dir = @scandir($this->folderPath);
        if ($dir === false) {
            return false;
        }
        $resultArray = array();
        foreach ($dir as $e) {
            if ($e != '.' && $e != '..') {
                if (is_dir($this->folderPath . '/' . $e)) {
                    $resultArray[] = new FolderImpl($this->folderPath . '/' . $e);
                } else {
                    $resultArray[] = new FileImpl($this->folderPath . '/' . $e);
                }
            }
        }
        return $resultArray;
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
        $r = ($this->exists() && @rename($this->folderPath, $path));
        if ($r) {
            $this->folderPath = $this->relativeToAbsolute($path);

        }
        return $r;
    }

    /**
     * @param string $path The path to the new file
     * @return null | Folder Return null on failure else an instance of File being the new file
     */
    public function copy($path)
    {
        $newFolder = new FolderImpl($path);
        if($newFolder->getAbsolutePath() == $this->getAbsolutePath()){
            return $newFolder;
        }
        if ($this->exists() && !file_exists($path)) {
            $newFolder->create();
            $this->recursiveCopyFolderContent($this->folderPath, $newFolder->getAbsolutePath());
            return $newFolder;
        }
        return null;
    }

    /**
     * @return string The name of the folder
     */
    public function getName()
    {
        $folderArray = explode('/', $this->folderPath);
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
        return $this->relativePath($this->folderPath, $dirName);
    }

    /**
     * @return Folder | null Will return Folder if parent folder exists else null
     */
    public function getParentFolder()
    {
        $p = dirname($this->folderPath);
        if ($p == $this->folderPath) {
            return null;
        }
        return new FolderImpl($p);
    }

    /**
     * @param int $mode Sets the mode, if recursive non empty folders can be deleted
     * @throws MalformedParameterException
     * @return bool Return TRUE if delete was successfully else FALSE
     */
    public function delete($mode = Folder::DELETE_FOLDER_NOT_RECURSIVE)
    {
        switch ($mode) {
            case Folder::DELETE_FOLDER_RECURSIVE:
                return @$this->recRmDir($this->folderPath);
                break;
            case Folder::DELETE_FOLDER_NOT_RECURSIVE:
                return @rmdir($this->folderPath);
                break;
            default:
                throw new MalformedParameterException('Folder[const]', 1);
        }
    }

    private function recRmDir($dir)
    {
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file))
                $this->recRmDir($file);
            else
                unlink($file);
        }
        return rmdir($dir);
    }

    private function recursiveCopyFolderContent($path1, $path2)
    {
        foreach (glob($path1 . '/*') as $file) {
            $fn = basename($file);
            if (is_dir($file)) {
                mkdir($path2 . '/' . $fn);
                $this->recursiveCopyFolderContent($path1 . '/' . $fn, $path2 . '/' . $fn);

            } else {
                copy($path1 . '/' . $fn, $path2 . '/' . $fn);
            }
        }
    }

}
