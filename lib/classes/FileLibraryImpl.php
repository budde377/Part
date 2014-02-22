<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/22/14
 * Time: 3:10 PM
 */

class FileLibraryImpl implements FileLibrary{
//    private $config;
    private $filesDir;
    private $whiteList = array();
    private $whitelistFile;


    function __construct(Folder $filesDir)
    {
//        $this->config = $config;
        $this->filesDir = $filesDir;
        $this->whitelistFile = new FileImpl($filesDir->getAbsolutePath()."/.whitelist");
        $this->loadWhitelist();

    }


    /**
     * Check if file is in library
     * @param File $file
     * @return bool TRUE if contained in library else FASLE
     */
    public function containsFile(File $file)
    {
        if(!$file->exists()){
            return false;
        }
        $p1 = $this->filesDir->getAbsolutePath();
        $p2 = $file->getParentFolder()->getParentFolder()->getAbsolutePath();
        return $p1 == $p2;
    }

    /**
     * @param File $file
     * @return bool TRUE if whitelisted and in library else FALSE
     */
    public function whitelistContainsFile(File $file)
    {
        return in_array($file->getRelativeFilePathTo($this->filesDir->getAbsolutePath()), $this->whiteList);
    }

    /**
     * @return array Returns an array containing whitelisted files in library.
     */
    public function getWhitelist()
    {
        return array_map(function($path){
            return new FileImpl($this->filesDir->getAbsolutePath()."/".$path);
        },$this->whiteList);
    }

    /**
     * @param User $user if null all files are returned else only files assoc with user are returned.
     * @return array Returns all files in library.
     */
    public function getFileList(User $user = null)
    {
        if($user != null){
            $folder = new FolderImpl($this->filesDir->getAbsolutePath()."/".$user->getUniqueId());
            if(!$folder->exists()){
                return array();
            }

            return $folder->listFolder();
        }
        $returnList = array();
        foreach($this->filesDir->listFolder(Folder::LIST_FOLDER_FOLDERS) as $folder){
            /** @var Folder $folder */
            $returnList = array_merge($returnList, $folder->listFolder());
        }
        return $returnList;
    }

    /**
     * @param File $file The file to whitelist
     * @return bool TRUE on success else FALSE
     */
    public function addToWhitelist(File $file)
    {
        if(!$this->containsFile($file)){
            return false;
        }

        $this->whiteList[] = $this->filePath($file);
        $this->writeWhitelist();
        return true;
    }

    /**
     * Remove a file from the whitelist
     * @param File $file
     * @return boolean TRUE on success else FALSE
     */
    public function removeFromWhitelist(File $file)
    {
        if(!$this->whitelistContainsFile($file)){
            return false;
        }

        if(($key = array_search($this->filePath($file), $this->whiteList)) !== false) {
            unset($this->whiteList[$key]);
        }
        $this->writeWhitelist();
        return true;
    }

    private function filePath(File $file){
        return $file->getRelativeFilePathTo($this->filesDir->getAbsolutePath());
    }

    /**
     * This will copy the file given to a implementation specific location.
     * @param User $user The uploading user
     * @param File $file The file to be uploaded.
     * @return File The location of the new file
     */
    public function addToLibrary(User $user, File $file)
    {
        if(!$this->filesDir->exists()){
            $this->filesDir->create();
        }
        $folder = new FolderImpl($this->filesDir->getAbsolutePath()."/".$user->getUniqueId());
        $folder->create();
        return $folder->putFile($file);

    }

    /**
     * This will clean the library from any file not present in the white-list
     * If the user argument is not null, only files uploaded by this user will be subject
     * to cleaning, else the whole library is checked.
     *
     * @param User $user
     * @return bool TRUE on success else FALSE
     */
    public function cleanLibrary(User $user = null)
    {
        foreach($this->getFileList($user) as $file){
            /** @var $file File */
            if(!$this->whitelistContainsFile($file)){
                $file->delete();
            }
        }
    }

    private function writeWhitelist(){
        if(!$this->filesDir->exists()){
            $this->filesDir->create();
        }

        $this->whitelistFile->delete();
        $this->whitelistFile->setAccessMode(File::FILE_MODE_RW_POINTER_AT_END);
        foreach($this->whiteList as $path){
            $this->whitelistFile->write($path."\n");
        }

    }

    private function loadWhitelist(){
        $this->whiteList = array_filter(explode("\n", $this->whitelistFile->getContents()));
    }
}