<?php
require_once dirname(__FILE__) . '/../_interface/File.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/1/12
 * Time: 10:31 AM
 * To change this template use File | Settings | File Templates.
 */
class FileImpl implements File
{

    protected $filePath;
    protected $mode = File::FILE_MODE_RW_POINTER_AT_END;

    /**
     * @param string $file
     */

    public function __construct($file)
    {

        $this->filePath = $this->relativeToAbsolute($file);
    }

    /**
     * Will return true if the file exists, elsdirname(__FILE__).'/_stub/test'e false
     * @return bool
     */
    public function fileExists()
    {
        $v = $this->isDirectory();
        $ret = file_exists($this->filePath) && !$this->isDirectory();
        return $ret;
    }

    /**
     * Will return a string with the file content or FALSE on failure.
     * If the file does not exists it will return empty string
     * @return string | bool
     */
    public function getContents()
    {
        if($this->isDirectory()){
            return false;
        }
        if ($this->fileExists()) {
            return file_get_contents($this->filePath);
        } else {
            return '';
        }
    }

    /**
     * Return the absolute path to the file.
     * @return string
     */
    public function getAbsoluteFilePath()
    {
        return $this->filePath;
    }

    /**
     * Get the relative path to some dir.
     *
     * @param string $dirName
     * @return string
     */
    public function getRelativeFilePathTo($dirName)
    {
        $absoluteDir = $this->relativeToAbsolute($dirName);
        $thisDirArray = explode('/', $this->filePath);
        $newDirArray = explode('/', $absoluteDir);
        $relativePath = '';
        $sizeToCut = 0;
        foreach ($newDirArray as $k => $p) {
            if (!isset($thisDirArray[$k]) || $p != $thisDirArray[$k]) {
                $relativePath .= '../';
            } else {
                $sizeToCut += strlen($p) + 1;
            }
        }
        $relativePath .= substr($this->filePath, $sizeToCut, strlen($this->filePath));
        return $relativePath;
    }

    /**
     * Will return the file name as a string
     * @return string
     */
    public function getFileName()
    {
        $pathArray = explode('/', $this->filePath);

        $last = array_pop($pathArray);

        return $last;
    }

    /**
     * Will move the file to specified path
     * @param string $path
     * @return bool TRUE if success FALSE if failure
     */
    public function move($path)
    {
        if($this->isDirectory()){
            return false;
        }
        $path = $this->relativeToAbsolute($path);
        if (($ret = @rename($this->filePath, $path)) === true) {
            $this->filePath = $path;
        }

        return $ret;
    }

    /**
     * @param $path
     * @return null | File Will return null on failure, else File being the new file
     */
    public function copy($path)
    {
        $newPath = $this->relativeToAbsolute($path);
        if (@copy($this->filePath, $newPath)) {
            return new FileImpl($newPath);
        }
        return null;

    }

    /**
     * Will delete the file
     * @return bool TRUE if success FALSE if failure
     */
    public function delete()
    {

        if ($this->isDirectory()) {
            return false;
        }
        return @unlink($this->filePath);
    }

    /**
     * Writes to file
     * @param $string
     * @return int | bool Returns the number of bytes written, or FALSE on error.
     */
    public function write($string)
    {
        $handle = @fopen($this->filePath, $this->mode);
        if ($handle === false) {
            return $handle;
        }
        return @fwrite($handle, $string);


    }

    /**
     * Sets the access mode, available options is in FileModeEnum
     * @param string $permissions
     * @return void
     */
    public function setAccessMode($permissions)
    {
        switch ($permissions) {
            case File::FILE_MODE_RW_POINTER_AT_END:
            case File::FILE_MODE_W_POINTER_AT_END:
            case File::FILE_MODE_R_POINTER_AT_BEGINNING:
            case File::FILE_MODE_RW_POINTER_AT_BEGINNING:
            case File::FILE_MODE_RW_TRUNCATE_FILE_TO_ZERO_LENGTH:
            case File::FILE_MODE_W_TRUNCATE_FILE_TO_ZERO_LENGTH:
                $this->mode = $permissions;

        }

    }

    protected function relativeToAbsolute($file)
    {


        if (substr($file, 0, 1) != '/') {
            $file = getcwd() . '/' . $file;
        }

        if (substr($file, -1, 1) == '/') {
            $file = substr($file, 0, strlen($file) - 1);
        }
        $fileArray = explode('/', $file);
        $pathSizeArray = array();
        $newFile = '';
        foreach ($fileArray as $p) {
            switch ($p) {
                case '.':
                    break;
                case '':
                    $newFile .= '/';
                    break;
                case '..':
                    $size = ($v = array_pop($pathSizeArray)) === null ? 0 : $v;
                    $newFile = substr($newFile, 0, ($size + 1) * -1);
                    break;
                default:
                    $newFile .= $p . '/';
                    array_push($pathSizeArray, strlen($p));
            }
        }
        $newFile = substr($newFile, 0, -1);
        return $newFile;

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
        if ($this->fileExists()) {
            return filesize($this->filePath);
        }
        return -1;
    }

    /**
     *
     * @return bool
     */
    protected function isDirectory()
    {
        return is_dir($this->filePath);
    }
}
