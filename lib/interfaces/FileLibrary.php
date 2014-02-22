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
     * @param User $user The uploading user
     * @param File $file The file to be uploaded.
     * @return bool TRUE on success else FALSE
     */
    public function addToLibrary(User $user, File $file);

    /**
     * This will clean the library from any file not present in the white-list
     * If the user argument is not null, only files uploaded by this user will be subject
     * to cleaning, else the whole library is checked.
     *
     * @param User $user
     * @return bool TRUE on success else FALSE
     */
    public function cleanLibrary(User $user = null);

}