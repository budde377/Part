<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 25/07/12
 * Time: 14:52
 */
interface XHTMLElement
{
    /**
     * @abstract
     * @return string
     */
    public function getXHTMLString();

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
     * @param XHTMLElement $element
     * @return void
     */
    public function insertXHTMLElement(XHTMLElement $element);

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