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
    public function getContent($id);

    /**
     * Returns and reuses instance of site scoped variables
     * @return Variables
     */
    public function getVariables();

    /**
     * Returns last modified timestamp, NULL if site hasn't been modified
     * @return int | null
     */
    public function lastModified();

    /**
     * "Modifies" the site by changing the last modified timestamp to now
     * @return int The new timestamp
     */
    public function modify();
}