<?php
namespace ChristianBudde\Part;
/**
 * User: budde
 * Date: 5/10/12
 * Time: 10:59 AM
 */
interface Config extends \ArrayAccess
{
    /**
     * @abstract
     * Will return the filename of a template file as a string.
     * This should only be a filename.
     * If the link is not in list, this will return null.
     * @param $name string
     * @return string | null
     */
    public function getTemplate($name);

    /**
     * Will return the name of the default template if defined. Else null.
     * @return string
     */
    public function getDefaultTemplateName();

    /**
     * Will return the default template if defined. Else null.
     * @return string
     */
    public function getDefaultTemplate();

    /**
     * Will path relative to project root to templates.
     * @param string $name The name of the template
     * @return null|string Null if template not defined
     */
    public function getTemplateFolderPath($name);

    /**
     * Lists the folders where to look for other templates.
     * @return string[]
     */
    public function listTemplateFolders();

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
    public function getPreTasks();


    /**
     * @abstract
     * Will return PostScripts as an array, with the ClassName as key and the link as value.
     * The link should be relative to a root path provided.
     * @return array
     */
    public function getPostTasks();



    /**
     * Will return AJAXTypeHandlers as an array, with the num key and an array containing "class_name" and "path" as value.
     * The link should be relative to a root path provided.
     * @return array
     */
    public function getAJAXTypeHandlers();

    /**
     * @abstract
     * @param string $name name of the pageElement as specified in config
     * @return array | null Array with entrance className, name, path with ClassName, name provided, and absolute path respectively.
     */
    public function getPageElement($name);

    /**
     * @abstract
     * @return array A map with entries: host, user, database, password and a list of folders.
     */
    public function getMySQLConnection();

    /**
     * @return bool
     */
    public function isDebugMode();

    /**
     * @return bool
     */
    public function isUpdaterEnabled();

    /**
     * @return string Root path
     */
    public function getRootPath();

    /**
     * @return string String containing the domain (name.ext)
     */
    public function getDomain();

    /**
     * @return array containing owner information
     */
    public function getOwner();

    /**
     * @return string Path to the tmp folder
     */
    public function getTmpFolderPath();

    /**
     * @return string Path to the error log.
     */
    public function getLogPath();

    /**
     * @return array
     */
    public function getVariables();

}
