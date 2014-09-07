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
        $returnList = array();
        if($user != null){
            $folder = new FolderImpl($this->filesDir->getAbsolutePath()."/".$user->getUniqueId());
            if(!$folder->exists()){
                return array();
            }
            $returnList = $folder->listFolder();
        } else {
            foreach($this->filesDir->listFolder(Folder::LIST_FOLDER_FOLDERS) as $folder){
                /** @var Folder $folder */
                $returnList = array_merge($returnList, $folder->listFolder());
            }

        }
        return array_filter($returnList, function(File $f){
            return !$this->isVersion($f);
        });
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

        $this->writeWhitelist($this->whiteList[] = $this->filePath($file));
        return $this->whitelistContainsFile($file);
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
        return !$this->whitelistContainsFile($file);
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
        $folder = new FolderImpl($this->filesDir->getAbsolutePath()."/".$user->getUniqueId());
        $ext = $file->getExtension() == ""?"":".".$file->getExtension();
        $name = str_replace(".", "", uniqid("",true)).$ext;
        return $this->addToLibraryHelper($folder, $file, $name);
    }

    private function addToLibraryHelper(Folder $folder, File $file, $newName){
        if(!$this->filesDir->exists()){
            $this->filesDir->create();
        }
        $folder->create();
        $f = $folder->putFile($file, $newName);
        return $f;
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
                /** @var $vf File */
                foreach($this->listVersionsToOriginal($file) as $vf){
                    $vf->delete();
                }
                $file->delete();
            }
        }
    }

    private function writeWhitelist($appendFile = null){
        if(!$this->filesDir->exists()){
            $this->filesDir->create();
        }

        if($appendFile != null){
            $this->whitelistFile->setAccessMode(File::FILE_MODE_RW_POINTER_AT_END);
            $this->whitelistFile->write($appendFile."\n");
            return;
        }
        $this->whitelistFile->setAccessMode(File::FILE_MODE_RW_TRUNCATE_FILE_TO_ZERO_LENGTH);
        $this->whitelistFile->write("");
        $this->whitelistFile->setAccessMode(File::FILE_MODE_RW_POINTER_AT_END);
        foreach($this->whiteList as $path){
            $this->whitelistFile->write($path."\n");
        }

    }

    private function loadWhitelist(){
        $this->whiteList = array_filter(explode("\n", $this->whitelistFile->getContents()));
    }

    /**
     * Will remove a file from library.
     * @param File $file
     * @return bool TRUE on success else FALSE
     */
    public function removeFromLibrary(File $file)
    {
        if(!$this->containsFile($file)){
            return false;
        }
        if($this->whitelistContainsFile($file)){
            $this->removeFromWhitelist($file);
        }
        $file->delete();
        return !$this->containsFile($file);
    }

    /**
     * A version of a file is a new file that shares the name of the original file plus some
     * appended version:
     *    original file:   file.txt
     *    Version 2    :   file-2.txt
     *
     * @param File $origFile
     * @param File $newFile
     * @param null|string $version
     * @return File
     */
    public function addVersionOfFile(File $origFile, File $newFile, $version = null)
    {
        if(!$this->containsFile($origFile)){
            return null;
        }

        $name = $this->versionFileName($origFile, $version);

        $f = new FileImpl($origFile->getParentFolder()->getAbsolutePath()."/".$name);
        if($f->exists()){
            return $f;
        }

        return $this->addToLibraryHelper($origFile->getParentFolder(),$newFile, $name);
    }

    private function versionFileName(File $f, $version = null){
        $version = $version == null?uniqid():$version;
        $ext = $f->getExtension() == ""?"":".".$f->getExtension();
        $name = $f->getBasename()."-".$version.$ext;
        return $name;
    }


    /**
     * Given a version file this will return the original.
     * If the given file isn't in the library, null will be returned.
     * If the given file isn't a version of a file, null will be returned.
     *
     * @param File $file
     * @return File
     */
    public function findOriginalFileToVersion(File $file)
    {
        if(!$this->containsFile($file)){
            return null;
        }

        preg_match("/([^-]+)(-.*)?/", $file->getBasename(), $matches);
        $ext = $file->getExtension() == ""?"":".".$file->getExtension();
        return new FileImpl($file->getParentFolder()->getAbsolutePath()."/".$matches[1].$ext);
    }

    /**
     * Will list the versions of a given file.
     * @param File $file
     * @return array
     */
    public function listVersionsToOriginal(File $file)
    {
        if(!$this->containsFile($file)){
            return array();
        }

        if($this->isVersion($file)){
            return array();
        }

        return array_filter($file->getParentFolder()->listFolder(Folder::LIST_FOLDER_FILES), function(File $f) use ($file){
            return $this->isVersion($f) && strpos($f->getBasename(), $file->getBasename()) === 0;
        });
    }


    /**
     * @param File $file
     * @return bool Returns TRUE if is version else FALSE
     */
    public function isVersion(File $file)
    {
        preg_match("/([^-]+)(-.*)?/", $file->getBasename(), $matches);
        return isset($matches[2]) && $matches[2] != "";

    }

    /**
     * Returns in which folder the files are located.
     * @return Folder
     */
    public function getFilesFolder()
    {
        return new FolderImpl($this->filesDir->getAbsolutePath());
    }

    /**
     * Will check if a version of the given file already exists.
     * @param File $file
     * @param string $version
     * @return bool
     */
    public function containsVersionOfFile($file, $version)
    {
        $f = $this->findVersionOfFile($file, $version);
        return  $f != null;
    }

    /**
     * This will return the desired version of the file, if it exists, else null.
     * @param File $file
     * @param string $version
     * @return File
     */
    public function findVersionOfFile($file, $version)
    {
        if($file == null || ($f2 = $this->findOriginalFileToVersion($file)) == null ||
            $file->getAbsoluteFilePath() != $f2->getAbsoluteFilePath()){
            return null;
        }
        $name = $this->versionFileName($file, $version);
        $f = new FileImpl($file->getParentFolder()->getAbsolutePath()."/".$name);
        return $this->containsFile($f)?$f:null;
    }

    /**
     * Returns the last time the whitelist was modified
     * @return int mixed
     */
    public function getWhitelistLastModified()
    {
        return $this->whitelistFile->getModificationTime();
    }

    /**
     * Will move a file to the library. It will use move_upload_file
     * function.
     * @param User $user The uploading user
     * @param File $file The file to be added
     * @return File Will return newly added file
     */
    public function uploadToLibrary(User $user, File $file)
    {
        $folder = new FolderImpl($this->filesDir->getAbsolutePath()."/".$user->getUniqueId());
        $ext = $file->getExtension() == ""?"":".".$file->getExtension();
        $name = str_replace(".", "", uniqid("",true)).$ext;
        if(!$this->filesDir->exists()){
            $this->filesDir->create();
        }
        if(!$folder->exists()){
            $folder->create();
        }
        $f = new FileImpl($folder->getAbsolutePath().'/'.$name);
        move_uploaded_file($file->getAbsoluteFilePath(),$f->getAbsoluteFilePath());
        return $f;
    }
}