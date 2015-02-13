<?php
namespace ChristianBudde\Part\model\site;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/23/13
 * Time: 1:16 PM
 */
use ChristianBudde\Part\model\Content;

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
     * @return \ChristianBudde\Part\model\Variables
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
     * @return \ChristianBudde\Part\model\ContentLibrary
     */
    public function getContentLibrary();
}