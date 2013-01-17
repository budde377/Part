<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/10/12
 * Time: 10:59 AM
 * To change this template use File | Settings | File Templates.
 */
interface Page
{

    const EVENT_ID_UPDATE = 1;
    const EVENT_DELETE = 2;

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
     * Will try and create the Page, if success will return TRUE, else FALSE.
     * If already exists will return FALSE.
     * @return bool
     */
    public function create();

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

}
