<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/23/13
 * Time: 1:16 PM
 */
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/23/13
 * Time: 1:12 PM
 */
interface Site
{
    /**
     * Returns and reuses instance of site scoped Content
     * @param string $id
     * @return Content
     */
    public function getContent($id = "");

    /**
     * Returns and reuses instance of site scoped variables
     * @return Variables
     */
    public function getVariables();

    /**
     * Returns last modified timestamp, 0 if site has not been modified
     * @return int
     */
    public function lastModified();

    /**
     * "Modifies" the site by changing the last modified timestamp to now
     * @return int The new timestamp
     */
    public function modify();


    /**
     * Will get and reuse instance of content library.
     * @return ContentLibrary
     */
    public function getContentLibrary();
}