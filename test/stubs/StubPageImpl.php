<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/20/12
 * Time: 10:18 PM
 * To change this template use File | Settings | File Templates.
 */
class StubPageImpl implements Page
{

    private $id;
    private $title;
    private $template;
    private $alias;
    private $hidden = false;
    private $lastModified = -1;
    private $pageContent  = array();
    private $variables ;


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
        $this->id = $id;
    }

    /**
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Set the template, the template should match element in config.
     * @param $template string
     * @return bool
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Will set the alias. This should be of format pattern in preg_match()
     * @param $alias string
     * @return bool
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
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
        return $id == $this->id || $id == $this->alias;
    }

    /**
     * Return TRUE if is editable, else FALSE
     * @return bool
     */
    public function isEditable()
    {
        return false;
    }

    /**
     * Check if given id is valid
     * @param String $id
     * @return bool
     */
    public function isValidId($id)
    {
        return false;
    }

    /**
     * Check if given alias is valid
     * @param String $alias
     * @return bool
     */
    public function isValidAlias($alias)
    {
        return false;
    }

    /**
     * @return bool Return TRUE if the page has been marked as hidden, else false
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * This will mark the page as hidden.
     * If the page is already hidden, nothing will happen.
     * @return void
     */
    public function hide()
    {
        $this->hidden = true;
    }

    /**
     * This will un-mark the page as hidden, iff it is hidden.
     * If the page is not hidden, nothing will happen.
     * @return void
     */
    public function show()
    {
        $this->hidden = false;
    }

    /**
     * This will return an object used to retrieve the content.
     * @param null | string $id Optional parameter specifying an id for the content.
     * @return Content
     */
    public function getContent($id = "")
    {
        return isset($this->pageContent[$id])?$this->pageContent[$id]:$this->pageContent[$id] = new StubContentImpl();
    }


    /**
     * Returns the time of last modification. This is for caching, and should reflect all content of the page.
     * @return int
     */
    public function lastModified()
    {
        return $this->lastModified;
    }

    /**
     * Will update the page with a new modify timestamp
     * @return int
     */
    public function modify()
    {
        return $this->lastModified = time();
    }

    /**
     * @return Variables Will return and reuse instance of variables
     */
    public function getVariables()
    {
        return $this->variables == null?$this->variables = new StubVariablesImpl(): $this->variables;
    }

    /**
     * Will return and reuse a ContentLibrary instance.
     * @return ContentLibrary
     */
    public function getContentLibrary()
    {
        return null;
    }
}
