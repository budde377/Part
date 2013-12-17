<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/10/12
 * Time: 10:53 AM
 * To change this template use File | Settings | File Templates.
 */
interface PageElement
{

    /**
     * @abstract
     * This will return content from page element as a string.
     * The format can be xml, xhtml, html etc. but return type must be string
     * @return string
     */
    public function generateContent();


    /**
     * Will set up the page element.
     * If you want to ensure that you register some files, this would be the place to do this.
     * This should always be called before generateContent, at the latest right before.
     * @return void
     */
    public function setUpElement();

    /**
     * Indicates whether the element has been set up before generate.
     * @return bool
     */
    public function hasBeenSetUp();
}
