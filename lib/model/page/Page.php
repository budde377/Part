<?php
namespace ChristianBudde\Part\model\page;
use ChristianBudde\Part\controller\ajax\TypeHandlerGenerator;
use ChristianBudde\Part\controller\json\JSONObjectSerializable;

/**
 * User: budde
 * Date: 5/10/12
 * Time: 10:59 AM
 */
interface Page extends JSONObjectSerializable, TypeHandlerGenerator
{

    const EVENT_ID_UPDATE = 1;

    /**
     * @return bool Return TRUE if the page has been marked as hidden, else false
     */
    public function isHidden();

    /**
     * This will mark the page as hidden.
     * If the page is already hidden, nothing will happen.
     * @return void
     */
    public function hide();

    /**
     * This will un-mark the page as hidden, iff it is hidden.
     * If the page is not hidden, nothing will happen.
     * @return void
     */
    public function show();

    /**
     * @abstract
     * @return string
     */
    public function getID();

    /**
     * @abstract
     * @return string
     */
    public function getTitle();

    /**
     * @abstract
     * The return string should match a template in some config.
     * @return string
     */
    public function getTemplate();

    /**
     * @abstract
     * This will return the alias as a string.
     * @return string
     */
    public function getAlias();

    /**
     * This will return an object used to retrieve the content.
     * @param null | string $id Optional parameter specifying an id for the content.
     * @return PageContent
     */
    public function getContent($id = "");


    /**
     * Will return and reuse a ContentLibrary instance.
     * @return PageContentLibrary
     */
    public function getContentLibrary();

    /**
     * @abstract
     * Set the id of the page. The ID should be of type [a-zA-Z0-9-_]+
     * If the id does not conform to above, it will return FALSE, else, TRUE
     * Also the ID must be unique, if not it will fail and return FALSE
     * @param $id string
     * @return bool
     */
    public function setID($id);

    /**
     * @abstract
     * @param string $title
     * @return void
     */
    public function setTitle($title);

    /**
     * @abstract
     * Set the template, the template should match element in config.
     * @param $template string
     * @return void
     */
    public function setTemplate($template);


    /**
     * @abstract
     * Will set the alias. This should be of format pattern in preg_match()
     * @param $alias string
     * @return bool
     */
    public function setAlias($alias);


    /**
     * @abstract
     * Will return TRUE if the page exists, else FALSE
     * @return bool
     */
    public function exists();


    /**
     * @abstract
     * Will delete the page from persistent storage
     * @return bool
     */
    public function delete();


    /**
     * @abstract
     * This will return TRUE if the $id match the page else FALSE.
     * @param $id string
     * @return bool
     */
    public function match($id);

    /**
     * Return TRUE if is editable, else FALSE
     * @return bool
     */
    public function isEditable();

    /**
     * Check if given id is valid
     * @param String $id
     * @return bool
     */
    public function isValidId($id);

    /**
     * Check if given alias is valid
     * @param String $alias
     * @return bool
     */
    public function isValidAlias($alias);

    /**
     * Will update the page with a new modify timestamp
     * @return int Last modified time
     */
    public function modify();

    /**
     * Returns the time of last modification. This is for caching, and should reflect all content of the page.
     * @return int
     */
    public function lastModified();

    /**
     * @return \ChristianBudde\Part\model\Variables Will return and reuse instance of variables
     */
    public function getVariables();

}
