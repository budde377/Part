<?php
require_once dirname(__FILE__) . '/../_trait/FileTrait.php';
require_once dirname(__FILE__) . '/../_interface/File.php';
require_once dirname(__FILE__) . '/FolderImpl.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/1/12
 * Time: 10:31 AM
 * To change this template use File | Settings | File Templates.
 */
class FileImpl implements File
{
    use FileTrait;
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
     * Will return true if the file exists, else false
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->filePath) && !$this->isDirectory();
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
        if ($this->exists()) {
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
        return $this->relativePath($this->filePath,$dirName);
    }

    /**
     * Will return the file name as a string
     * @return string
     */
    public function getBaseName()
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
        if ($this->exists()) {
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

    /**
     * @return Folder Will return parent folder
     */
    public function getParentFolder()
    {
        return new FolderImpl(dirname($this->filePath));
    }

    /**
     * @return resource | bool Returns a file pointer resource on success, or FALSE on error.
     */
    public function getResource()
    {
        return fopen($this->filePath,$this->mode);
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return pathinfo($this->filePath, PATHINFO_EXTENSION);
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return pathinfo($this->filePath, PATHINFO_FILENAME);
    }

    /**
     * @return string | null Will return string if available.
     */
    public function getMimeType()
    {
        $m = @finfo_file(finfo_open(FILEINFO_MIME_TYPE), $this->filePath);
        return $m?$m:null;
    }

    /**
     * @return string
     */
    public function getDataURI()
    {
        $contents = base64_encode($this->getContents());
        return ($m = $this->getMimeType()) == null?null:"data:{$this->getMimeType()};base64,$contents";
    }
}
