<?php
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
        return false;
    }

    /**
     * This will mark the page as hidden.
     * If the page is already hidden, nothing will happen.
     * @return void
     */
    public function hide()
    {
    }

    /**
     * This will un-mark the page as hidden, iff it is hidden.
     * If the page is not hidden, nothing will happen.
     * @return void
     */
    public function show()
    {
    }

    /**
     * This will return an object used to retrieve the content.
     * @param null | string $id Optional parameter specifying an id for the content.
     * @return Content
     */
    public function getContent($id = "")
    {
        return new NullContentImpl();
    }

    /**
     * Returns the time of last modification. This is for caching, and should reflect all content of the page.
     * @return int
     */
    public function lastModified()
    {
        return -1;
    }

    /**
     * Will update the page with a new modify timestamp
     * @return void
     */
    public function modify()
    {
    }

    /**
     * @return Variables Will return and reuse instance of variables
     */
    public function getVariables()
    {
        return null;
    }

    /**
     * Will return and reuse a ContentLibrary instance.
     * @return ContentLibrary
     */
    public function getContentLibrary()
    {
        return null;
    }

    /**
     * Serializes the object to an instance of JSONObject.
     * @return JSONObject
     */
    public function jsonObjectSerialize()
    {
        return new PageJSONObjectImpl($this);
    }


    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return $this->jsonObjectSerialize()->jsonSerialize();
    }
}
