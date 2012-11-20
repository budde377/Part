<?php
require_once dirname(__FILE__) . '/../_class/ConnectionFileImpl.php';
require_once dirname(__FILE__) . '/../_interface/Folder.php';
require_once dirname(__FILE__) . '/../_trait/FilePathTrait.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/28/12
 * Time: 11:54 AM
 * To change this template use File | Settings | File Templates.
 */
class ConnectionFolderImpl implements Folder
{

    use FilePathTrait;

    private $path;
    private $connection;
    /** @var Iterator */
    private $folderIterator;

    public function __construct($path, Connection $connection)
    {
        $this->path = $this->relativeToAbsolute($path, '/');
        $this->connection = $connection;
        $this->setUpIterator();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->folderIterator->current();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->folderIterator->next();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->folderIterator->key();
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
        return $this->folderIterator->valid();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->setUpIterator();
    }

    /**
     * @return array | bool Will return an array containing Folders and Files or FALSE on failure
     */
    public function listFolder()
    {
        if ($this->connection->isDirectory($this->path)) {
            $res = array();
            foreach ($this->connection->listDirectory($this->path) as $element) {
                $path = $this->relativeToAbsolute($this->path . '/' . $element);
                if ($this->connection->isDirectory($path)) {
                    $res[] = new ConnectionFolderImpl($path, $this->connection);
                } else if ($this->connection->isFile($path)) {
                    $res[] = new ConnectionFileImpl($path, $this->connection);
                }

            }
            return $res;
        }
        return false;
    }

    /**
     * @param int $mode Sets the mode, if recursive non empty folders can be deleted
     * @return bool Return TRUE if delete was successfully else FALSE
     */
    public function delete($mode = Folder::DELETE_FOLDER_NOT_RECURSIVE)
    {
        if ($this->connection->isDirectory($this->path)) {
            switch ($mode) {
                case Folder::DELETE_FOLDER_NOT_RECURSIVE:
                    $l = $this->listFolder();
                    if(is_array($l) && count($l)==0){
                        return $this->connection->deleteDirectory($this->path);
                    }

                    break;
                case Folder::DELETE_FOLDER_RECURSIVE:
                    $l = $this->listFolder();
                    if(is_array($l)){
                        $res = true;
                        foreach($l as $element){
                            if($element instanceof File){
                                $res = $res && $element->delete();
                            } else if($element instanceof Folder){
                                $res = $res && $element->delete($mode);
                            }
                        }
                        return $res && $this->connection->deleteDirectory($this->path);
                    }

                    break;
            }
        }
        return false;
    }

    /**
     * @return bool Return TRUE if folder exists else FALSE
     */
    public function exists()
    {
        return $this->connection->isDirectory($this->path) && $this->connection->exists($this->path);
    }

    /**
     * @return bool Return TRUE on success else FALSE
     */
    public function create()
    {
        $this->setUpIterator();
        return $this->connection->createDirectory($this->path);
    }

    /**
     * @param string $path The path to the new file
     * @return bool Return TRUE on success else FALSE
     */
    public function move($path)
    {
        $path = $this->relativeToAbsolute($path, '/');
        if ($this->connection->isDirectory($this->path) && $this->connection->move($this->path, $path)) {
            $this->path = $path;
            return true;
        }
        return false;
    }

    /**
     * @param string $path The path to the new file
     * @return null | Folder Return null on failure else an instance of File being the new file
     */
    public function copy($path)
    {
        $path = $this->relativeToAbsolute($path, '/');
        if ($this->connection->isDirectory($this->path) && $this->connection->copy($this->path, $path)) {
            return new ConnectionFolderImpl($path, $this->connection);
        }
        return null;
    }

    /**
     * @return string The name of the folder
     */
    public function getName()
    {
        return basename($this->path);
    }

    /**
     * @return string Will return the absolute path to the folder
     */
    public function getAbsolutePath()
    {
        return $this->path;
    }

    /**
     * @param string $dirName
     * @return string The relative path to provided dirName
     */
    public function getRelativePathTo($dirName)
    {
        return $this->relativePath($this->path, $dirName, '/');
    }

    /**
     * @return Folder | null Will return Folder if parent folder exists else null
     */
    public function getParentFolder()
    {
        $parentFolder = dirname($this->path);
        return $parentFolder == $this->path ? null : new ConnectionFolderImpl($parentFolder, $this->connection);
    }

    /**
     * Will put a folder to the folder (copy the folder into current folder)
     * @param Folder $folder
     * @param null $newName The new name of the folder, if Null then new folder will preserve name
     * @return bool Return TRUE on success and FALSE on failure
     */
    public function putFolder(Folder $folder, $newName = null)
    {
        // TODO: Implement putFolder() method.
    }

    /**
     * Will put a folder to the folder (copy the folder into current folder)
     * @param File $file
     * @param null $newName The new name of the file, if Null then new file will preserve name
     * @return bool Return TRUE on success and FALSE on failure
     */
    public function putFile(File $file, $newName = null)
    {
        // TODO: Implement putFile() method.
    }

    private function setUpIterator(){
        $array = $this->listFolder();
        $arrayObject = new ArrayObject($array == false? array():$array);
        $this->folderIterator = $arrayObject->getIterator();
    }
}
