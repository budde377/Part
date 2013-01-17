<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/10/12
 * Time: 10:59 AM
 * To change this template use File | Settings | File Templates.
 */
interface Config
{
    /**
     * @abstract
     * Will return the link to the template file as a string.
     * This should be relative to a root path provided.
     * If the link is not in list, this will return null.
     * @param $name string
     * @return string | null
     */
    public function getTemplate($name);

    /**
     * Will return a array containing all possible templates by name.
     * @return array
     */
    public function listTemplateNames();

    /**
     * Will return an array with default pages. Pages hardcoded into the website.
     * The array will have the page title as key and another array, containing keys [template], [alias] and [id], as value.
     * @return array
     */
    public function getDefaultPages();

    /**
     * @abstract
     * Will return PreScripts as an array, with the ClassName as key and the link as value.
     * The link should be relative to a root path provided.
     * @return array
     */
    public function getPreScripts();


    /**
     * @abstract
     * Will return PostScripts as an array, with the ClassName as key and the link as value.
     * The link should be relative to a root path provided.
     * @return array
     */
    public function getPostScripts();


    /**
     * @abstract
     * @param string $name name of the pageElement as specified in config
     * @return array | null Array with entrance className, name, path with ClassName, name provided, and absolute path respectively.
     */
    public function getPageElement($name);

    /**
     * @abstract
     * @param $name
     * @return array | null Array with entrance className, name, path with ClassName, name provided, and absolute path respectively.
     */
    public function getOptimizer($name);

    /**
     * @abstract
     * @return array | null Array with entries host, user, password, prefix, database, or null if not specified
     */
    public function getMySQLConnection();

}
