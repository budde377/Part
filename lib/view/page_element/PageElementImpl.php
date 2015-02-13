<?php
namespace ChristianBudde\Part\view\page_element;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/28/13
 * Time: 10:25 AM
 */

class PageElementImpl implements PageElement{

    private $setUp = false;

    /**
     * This will return content from page element as a string.
     * The format can be xml, xhtml, html etc. but return type must be string
     * @return string
     */
    public function generateContent()
    {
        if(!$this->hasBeenSetUp()){
            $this->setUpElement();
        }
        $this->setUp = false;
    }

    /**
     * Will set up the page element.
     * If you want to ensure that you register some files, this would be the place to do this.
     * This should always be called before generateContent, at the latest right before.
     * @return void
     */
    public function setUpElement()
    {
        $this->setUp = true;
    }

    /**
     * Indicates whether the element has been set up before generate.
     * @return bool
     */
    public function hasBeenSetUp()
    {
        return $this->setUp;
    }
}