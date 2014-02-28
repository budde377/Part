<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/22/14
 * Time: 2:52 PM
 */

interface FileLibrary {

    /**
     * Check if file is in library
     * @param File $file
     * @return bool TRUE if contained in library else FASLE
     */
    public function containsFile(File $file);

    /**
     * @param File $file
     * @return bool TRUE if whitelisted and in library else FALSE
     */
    public function whitelistContainsFile(File $file);


    /**
     * @return array Returns an array containing whitelisted files in library.
     */
    public function getWhitelist();


    /**
     * @param User $user if null all files are returned else only files assoc with user are returned.
     * @return array Returns all files in library.
     */
    public function getFileList(User $user = null);

    /**
     * @param File $file The file to whitelist
     * @return bool TRUE on success else FALSE
     */
    public function addToWhitelist(File $file);

    /**
     * Adds a file to the whitelist
     * @param File $file
     * @return boolean TRUE on success else FALSE
     */
    public function removeFromWhitelist(File $file);

    /**
     * Will copy a file to the library.
     * The file will not necessary preserve its filename.
     * @param User $user The uploading user
     * @param File $file The file to be uploaded.
     * @return File | null Will return the newly added file.
     */
    public function addToLibrary(User $user, File $file);

    /**
     * Will remove a file from library.
     * @param File $file
     * @return bool TRUE on success else FALSE
     */
    public function removeFromLibrary(File $file);

    /**
     * This will clean the library from any file not present in the white-list
     * If the user argument is not null, only files uploaded by this user will be subject
     * to cleaning, else the whole library is checked.
     *
     * @param User $user
     * @return bool TRUE on success else FALSE
     */
    public function cleanLibrary(User $user = null);


    /**
     * A version of a file is a new file that shares the name of the original file plus some
     * appended version:
     *    original file:   file.txt
     *    Version 2    :   file-2.txt
     *
     * @param File $origFile
     * @param File $newFile
     * @param null $version
     * @return File
     */
    public function addVersionOfFile(File $origFile, File $newFile, $version = null);


    /**
     * Given a version file this will return the original.
     * If the given file isn't in the library, null will be returned.
     * If the given file isn't a version of a file, null will be returned.
     *
     * @param File $file
     * @return File
     */
    public function findOriginalFileToVersion(File $file);


    /**
     * Will list the versions of a given file.
     * @param File $file
     * @return array
     */
    public function listVersionsToOriginal(File $file);


    /**
     * @param File $file
     * @return bool Returns TRUE if is version else FALSE
     */
    public function isVersion(File $file);


    /**
     * Returns in which folder the files are located.
     * @return Folder
     */
    public function getFilesFolder();

    /**
     * Will check if a version of the given file already exists.
     * @param File $file
     * @param string $version
     * @return bool
     */
    public function containsVersionOfFile($file, $version);


    /**
     * This will return the desired version of the file, if it exists, else null.
     * @param File $file
     * @param string $version
     * @return File
     */
    public function findVersionOfFile($file, $version);

}