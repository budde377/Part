<?php
namespace ChristianBudde\cbweb\view\html;


/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 19/01/13
 * Time: 17:00
 */
class OptionElementImpl implements OptionElement
{
    private $element;

    function __construct($text,$value,$group_id = null,$attributes = array())
    {
        $this->element = new ElementImpl("option",$attributes);
        $this->element->setAttributes("value",$value);
        $this->element->insertString($text);
    }


    /**
     * @return string
     */
    public function getHTMLString()
    {
        return $this->element->getHTMLString();
    }

    /**
     * @param string $attribute
     * @param string $value
     * @return void
     */
    public function setAttributes($attribute, $value)
    {
        $this->element->setAttributes($attribute,$value);
    }

    /**
     * @param string $attribute
     * @return string | null Null if attribute not set, else string
     */
    public function getAttributes($attribute)
    {
        return $this->element->getAttributes($attribute);
    }

    /**
     * Inserts an XHTML Element
     * @param Element $element
     * @return void
     */
    public function insertHTMLElement(Element $element)
    {
        $this->element->insertHTMLElement($element);
    }

    /**
     * Insert an string
     * @param string $string
     * @return mixed
     */
    public function insertString($string)
    {
        $this->element->insertString($string);
    }

    /**
     * Clears the content of the Element
     * @return void
     */
    public function clearContent()
    {
        $this->element->clearContent();
    }

    /**
     * Set selected
     * @param bool $selected
     * @return void
     */
    public function setSelected($selected)
    {
        $this->element->setAttributes("selected",$selected?"selected":"");
    }
}
