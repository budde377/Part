<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 31/08/12
 * Time: 21:46
 */
class HTMLSelectElementImpl implements HTMLSelectElement
{

    private $element;
    private $groups = array();

    public function HTMLSelectElementImpl($name, $id, $multiple = false, $size = 1, $disabled = false)
    {
        $this->element = new HTMLElementImpl('select',array('name'=>$name,'id'=>$id,'size'=>$size));
        if($multiple){
            $this->element->setAttributes('multiple','multiple');
        }
        if($disabled){
            $this->element->setAttributes('disabled','disabled');
        }
        $this->element->insertString('<!-- -->');
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
     * @param HTMLElement $element
     * @return void
     */
    public function insertHTMLElement(HTMLElement $element)
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
     * @param string $text
     * @param string $value
     * @param string $group_id
     * @param array $attributes
     * @return HTMLOptionElement
     */
    public function insertOption($text, $value, $group_id = null, array $attributes = array())
    {
        $option = new HTMLOptionElementImpl($text,$value,$group_id,$attributes);
        if($group_id != null && isset($this->groups[$group_id])){
            /** @var $group HTMLElement */
            $group = $this->groups[$group_id];
            $group->insertHTMLElement($option);
        } else {
            $this->element->insertHTMLElement($option);
        }
        return $option;
    }

    /**
     * @param $id
     * @param string $label
     * @return HTMLElement
     */
    public function insertOptionGroup($id, $label)
    {
        $group = new HTMLElementImpl('optgroup',array('label'=>$label));
        $this->groups[$id] = $group;
        $this->element->insertHTMLElement($group);
        return $group;
    }


    /**
     * @param boolean $multiple
     * @return mixed
     */
    public function setMultiple($multiple)
    {
        $this->element->setAttributes('multiple',$multiple?'multiple':'');
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function setName($name)
    {
        $this->element->setAttributes('name',$name);
    }

    /**
     * @param int $size
     * @return void
     */
    public function setSize($size)
    {
        $this->element->setAttributes('size',$size);
    }

    /**
     * @param boolean $disabled
     * @return void
     */
    public function setDisabled($disabled)
    {
        $this->element->setAttributes('disabled',$disabled?'disabled':'');
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function setId($id)
    {
        $this->element->setAttributes('id',$id);
    }
}
