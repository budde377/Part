<?php
require_once dirname(__FILE__) . '/../_interface/XHTMLElement.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 25/07/12
 * Time: 15:19
 */
class XHTMLElementImpl implements XHTMLElement
{
    private $tagName;
    private $attributes;
    private $content = array();

    public function __construct($tagName,array $attributes = array()){
        $this->tagName = $tagName;
        $this->attributes = $attributes;
    }

    /**
     * @return string
     */
    public function getXHTMLString()
    {
        $attributeString = '';
        foreach($this->attributes as $attribute=>$value){
            $value = htmlspecialchars($value);
            $attributeString .= " $attribute='$value'";
        }
        $contentString = '';
        foreach($this->content as $content){
            /** @var $content XHTMLElement|string */
            if($content instanceof XHTMLElement){
                $contentString .= $content->getXHTMLString();
            } else {
                $contentString .= $content;
            }
        }

        if($contentString == '' && $attributeString == ''){
            return '';
        } else if($contentString == ''){
            return "<{$this->tagName}$attributeString />";
        } else {
            return "<{$this->tagName}$attributeString>$contentString</{$this->tagName}>";
        }
    }

    /**
     * @param string $attribute
     * @param string $value
     * @return void
     */
    public function setAttributes($attribute, $value)
    {
        if(empty($value)){
            unset($this->attributes[$attribute]);
        } else {
            $this->attributes[$attribute] = $value;
        }
    }

    /**
     * @param string $attribute
     * @return string | null
     */
    public function getAttributes($attribute)
    {
        return isset($this->attributes[$attribute]) ? $this->attributes[$attribute]:null;

    }

    /**
     * Inserts an XHTML Element
     * @param XHTMLElement $element
     * @return XHTMLElement
     */
    public function insertXHTMLElement(XHTMLElement $element)
    {
        $this->content[] = $element;
    }

    /**
     * Insert an string
     * @param string $string
     * @return mixed
     */
    public function insertString($string)
    {
        $this->content[] = $string;
    }

    /**
     * Clears the content of the Element
     * @return void
     */
    public function clearContent()
    {
        $this->content = array();
    }
}
