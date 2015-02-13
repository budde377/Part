<?php
namespace ChristianBudde\Part\view\html;/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 25/07/12
 * Time: 14:52
 */
interface Element
{
    /**
     * @abstract
     * @return string
     */
    public function getHTMLString();

    /**
     * @abstract
     * @param string $attribute
     * @param string $value
     * @return void
     */
    public function setAttributes($attribute,$value);

    /**
     * @abstract
     * @param string $attribute
     * @return string | null Null if attribute not set, else string
     */
    public function getAttributes($attribute);

    /**
     * @abstract
     * Inserts an XHTML Element
     * @param Element $element
     * @return void
     */
    public function insertHTMLElement(Element $element);

    /**
     * @abstract
     * Insert an string
     * @param string $string
     * @return mixed
     */
    public function insertString($string);

    /**
     * @abstract
     * Clears the content of the Element
     * @return void
     */
    public function clearContent();
}
