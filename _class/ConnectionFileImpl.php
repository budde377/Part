<?php
require_once dirname(__FILE__) .'/ConnectionFolderImpl.php';
require_once dirname(__FILE__) . '/../_interface/File.php';
require_once dirname(__FILE__) . '/../_trait/FileTrait.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/28/12
 * Time: 11:54 AM
 * To change this template use File | Settings | File Templates.
 */
class ConnectionFileImpl implements File
{
    use FileTrait;

    private $mode = File::FILE_MODE_RW_POINTER_AT_END;
    private $connection;
    private $path;

    public function __construct($path, Connection $connection)
    {
        $this->connection = $connection;
        $this->path = $this->relativeToAbsolute($path, '/');
    }

    /**
     * Will return true if the file exists, else false
     * @return bool
     */
    public function exists()
    {
        return $this->connection->exists($this->path) && $this->connection->isFile($this->path);
    }

    /**
     * Will return a string with the file content or FALSE on failure.
     * If the file does not exists it will return empty string
     * @return string | bool
     */
    public function getContents()
    {
        $res = $this->connection->exists($this->path) ? @file_get_contents($this->connection->getWrapper() . $this->path) : '';
        return $this->connection->isDirectory($this->path) ? false : $res;
    }

    /**
     * Get the relative path to some dir.
     *
     * @param string $dirName
     * @return string
     */
    public function getRelativeFilePathTo($dirName)
    {
        return $this->relativePath($this->path, $dirName, '/');
    }

    /**
     * Return the absolute path to the file.
     * @return string
     */
    public function getAbsoluteFilePath()
    {
        return $this->path;
    }

    /**
     * Will return the file name as a string
     * @return string
     */
    public function getBaseName()
    {
        return basename($this->path);
    }

    /**
     * Will move the file to specified path
     * @param string $path
     * @return bool TRUE if success FALSE if failure
     */
    public function move($path)
    {
        $path = $this->relativeToAbsolute($path,'/');
        if($this->connection->isFile($this->path) && $this->connection->move($this->path,$path)){
            $this->path = $path;
            return true;
        }
        return false;
    }

    /**
     * @param $path
     * @return null | File Will return null on failure, else File being the new file
     */
    public function copy($path)
    {
        $path = $this->relativeToAbsolute($path,'/');
        if($this->connection->isFile($this->path) && $this->connection->copy($this->path,$path)){
            return new ConnectionFileImpl($path,$this->connection);
        }
        return null;
    }

    /**
     * Will delete the file
     * @return bool TRUE if success FALSE if failure
     */
    public function delete()
    {
        return $this->connection->isFile($this->path) && $this->connection->deleteFile($this->path);
    }

    /**
     * Writes to file
     * @param $string
     * @return int | bool Returns the number of bytes written, or FALSE on error.
     */
    public function write($string)
    {
        $handle = @fopen($this->path, $this->mode);
        if ($handle === false) {
            return false;
        }
        return  $handle = @fopen($this->path, $this->mode) !== false? @fwrite($handle, $string): false;
    }

    /**
     * Sets the access mode, available options is in FileModeEnum
     * @param string $permissions
     * @return void
     */
    public function setAccessMode($permissions)
    {
        switch ($permissions) {
            case File::FILE_MODE_W_POINTER_AT_END:
            case File::FILE_MODE_RW_POINTER_AT_END:
            case File::FILE_MODE_R_POINTER_AT_BEGINNING:
            case File::FILE_MODE_RW_POINTER_AT_BEGINNING:
            case File::FILE_MODE_W_TRUNCATE_FILE_TO_ZERO_LENGTH:
            case File::FILE_MODE_RW_TRUNCATE_FILE_TO_ZERO_LENGTH:
                $this->mode = $permissions;
        }
    }

    /**
     * Gets the current accessMode
     * @return string
     */
    public function getAccessMode()
    {
        return $this->mode;
    }

    /**
     * Gets the file size in bytes
     * @return int
     */
    public function size()
    {
        return $this->connection->size($this->path);
    }

    /**
     * @return Folder Will return parent folder
     */
    public function getParentFolder()
    {
        $dir = dirname($this->path);
        return $dir == $this->path? null : new ConnectionFolderImpl($dir,$this->connection);
    }

    /**
     * @return resource | bool Returns a file pointer resource on success, or FALSE on error.
     */
    public function getResource()
    {
        return @fopen($this->connection->getWrapper() . $this->path, $this->mode);
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return pathinfo($this->path, PATHINFO_FILENAME);
    }

    /**
     * @return string | null Will return string if available.
     */
    public function getMimeType()
    {

    }

    /**
     * @return string
     */
    public function getDataURI()
    {

    }
}
