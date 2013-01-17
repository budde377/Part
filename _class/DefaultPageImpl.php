<?php
require_once dirname(__FILE__).'/../_interface/Page.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 17/01/13
 * Time: 21:18
 */
class DefaultPageImpl implements Page
{

    private $template,$title,$alias,$id;
    public function __construct($id,$title,$template,$alias = null)
    {
        $this->id = $id;
        $this->template = $template;
        $this->title = $title;
        $this->alias = @preg_match($alias,"") === false?'':$alias;
    }


    /**
     * @return string
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * The return string should match a template in some config.
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * This will return the alias as a string.
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set the id of the page. The ID should be of type [a-zA-Z0-9-_]+
     * If the id does not conform to above, it will return FALSE, else, TRUE
     * Also the ID must be unique, if not it will fail and return FALSE
     * @param $id string
     * @return bool
     */
    public function setID($id)
    {
        return false;
    }

    /**
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {

    }

    /**
     * Set the template, the template should match element in config.
     * @param $template string
     * @return void
     */
    public function setTemplate($template)
    {
    }

    /**
     * Will set the alias. This should be of format pattern in preg_match()
     * @param $alias string
     * @return bool
     */
    public function setAlias($alias)
    {
        return false;
    }

    /**
     * Will return TRUE if the page exists, else FALSE
     * @return bool
     */
    public function exists()
    {
        return false;
    }

    /**
     * Will try and create the Page, if success will return TRUE, else FALSE.
     * If already exists will return FALSE.
     * @return bool
     */
    public function create()
    {
        return false;
    }

    /**
     * Will delete the page from persistent storage
     * @return bool
     */
    public function delete()
    {
        return false;
    }

    /**
     * This will return TRUE if the $id match the page else FALSE.
     * @param $id string
     * @return bool
     */
    public function match($id)
    {
        return $id == $this->id || (strlen($this->getAlias()) && @preg_match($this->getAlias(), $id));
    }

    /**
     * Return TRUE if is editable, else FALSE
     * @return bool
     */
    public function isEditable()
    {
        return false;
    }
}
