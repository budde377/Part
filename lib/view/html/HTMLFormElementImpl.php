<?php
namespace ChristianBudde\cbweb\view\html;
use ChristianBudde\cbweb\exception\MalformedParameterException;


/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 25/07/12
 * Time: 15:34
 */
class HTMLFormElementImpl implements HTMLFormElement
{

    private $formElement;
    /** @var $notion HTMLElement | null*/
    private $notion;

    public function __construct($method = HTMLFormElement::FORM_METHOD_GET, $action = "#")
    {
        $this->formElement = new HTMLElementImpl('form', array('action' => $action, 'method' => $method));
        $this->notion = new HTMLElementImpl('span');
        $this->formElement->insertHTMLElement($this->notion);
    }

    /**
     * @return string
     */
    public function getHTMLString()
    {

        return $this->formElement->getHTMLString();
    }

    /**
     * @param string $attribute
     * @param string $value
     * @return void
     */
    public function setAttributes($attribute, $value)
    {
        $this->formElement->setAttributes($attribute, $value);
    }

    /**
     * @param string $attribute
     * @return string | null Null if attribute not set, else string
     */
    public function getAttributes($attribute)
    {
        return $this->formElement->getAttributes($attribute);
    }

    /**
     * Inserts an XHTML Element
     * @param HTMLElement $element
     * @return HTMLElement
     */
    public function insertHTMLElement(HTMLElement $element)
    {
        $this->formElement->insertHTMLElement($element);
    }

    /**
     * Insert an string
     * @param string $string
     * @return mixed
     */
    public function insertString($string)
    {
        $this->formElement->insertString($string);
    }

    /**
     * Sets the form method, must either be post or get
     * if none of those exception will be thrown
     * @param string $method
     * @throws \ChristianBudde\cbweb\exception\MalformedParameterException
     * @return void
     */
    public function setMethod($method = HTMLFormElementImpl::FORM_METHOD_GET)
    {
        if ($method != HTMLFormElement::FORM_METHOD_GET && $method != HTMLFormElement::FORM_METHOD_POST) {
            throw new MalformedParameterException('XHTMLFormElement[FORM_METHOD_GET|FORM_METHOD_POST]', 1);
        }
        $this->formElement->setAttributes('method', $method);

    }

    /**
     * @param string $action
     * @return void
     */
    public function setAction($action)
    {
        $this->formElement->setAttributes('action', $action);
    }

    /**
     * Sets the notion, this can be info, error or success.
     * If none of those is given, exception will be thrown.
     * @param string $notion
     * @param string $type
     * @throws MalformedParameterException
     * @return void
     */
    public function setNotion($notion, $type = HTMLFormElementImpl::NOTION_TYPE_INFORMATION)
    {
        if ($type != HTMLFormElement::NOTION_TYPE_ERROR && $type != HTMLFormElement::NOTION_TYPE_INFORMATION &&
            $type != HTMLFormElement::NOTION_TYPE_SUCCESS
        ) {
            throw new MalformedParameterException('XHTMLFormElement[NOTION_TYPE_ERROR|NOTION_TYPE_INFORMATION|NOTION_TYPE_SUCCESS]', 2);
        }
        $this->notion->setAttributes('class', 'notion ' . $type);
        $this->notion->clearContent();
        $this->notion->insertString($notion);
    }

    /**
     * Will insert an input text
     * @param string $value
     * @param string $name
     * @param string $id
     * @param string $label
     * @param array $attributes
     * @return HTMLElement
     */
    public function insertInputText($name, $id, $value = '', $label = '', array $attributes = array())
    {
        $container = new HTMLElementImpl('div', array('class' => 'text'));
        $input = new HTMLElementImpl('input', array('type' => 'text', 'name' => $name, 'value' => $value, 'id' => $id));
        $this->insertAttributesNot($input, $attributes, array('type', 'name', 'value', 'id'));
        $labelElement = new HTMLElementImpl('label', array('for' => $id));
        $labelElement->insertString($label);
        $container->insertHTMLElement($labelElement);
        $container->insertHTMLElement($input);
        $this->formElement->insertHTMLElement($container);
        return $container;
    }


    /**
     * @param string $value
     * @param string $name
     * @param array $attributes
     * @return HTMLElement
     */
    public function insertInputHidden($value, $name, array $attributes = array())
    {
        $input = new HTMLElementImpl('input', array('type' => 'hidden', 'value' => $value, 'name' => $name));
        $this->insertAttributesNot($input, $attributes, array('type', 'value', 'name'));
        $this->formElement->insertString($input);
        return $input;
    }


    /**
     * Will insert an input submit
     * @param string $value
     * @param array $attributes
     * @return HTMLElement
     */
    public function insertInputSubmit($value, array $attributes = array())
    {
        $input = new HTMLElementImpl('input', array('value' => $value, 'type' => 'submit'));
        $this->insertAttributesNot($input, $attributes, array('value', 'name', 'type'));
        $container = new HTMLElementImpl('div',array('class'=>'submit'));
        $container->insertHTMLElement($input);
        $this->formElement->insertHTMLElement($container);
        return $container;
    }

    /**
     * @param string $name
     * @param string $id
     * @param string $label
     * @param array $attributes
     * @return HTMLElement
     */
    public function insertInputFileUpload($name, $id, $label = '', array $attributes = array())
    {
        $container = new HTMLElementImpl('div', array('class' => 'fileUpload'));
        $input = new HTMLElementImpl('input', array('type' => 'file', 'id' => $id,'name'=>$name));
        $this->insertAttributesNot($input, $attributes, array('type', 'id'));
        $labelElement = new HTMLElementImpl('label', array('for' => $id));
        $labelElement->insertString($label);
        $container->insertHTMLElement($labelElement);
        $container->insertHTMLElement($input);
        $this->formElement->insertHTMLElement($container);
        $this->formElement->setAttributes('enctype','multipart/form-data');
        return $container;
    }

    /**
     * Clears the content of the Element
     * @return void
     */
    public function clearContent()
    {
        $this->formElement->setAttributes('enctype','');
        $this->formElement->clearContent();
    }

    /**
     * Will insert an input password.
     * @param string $value
     * @param string $name
     * @param string $id
     * @param string $label
     * @param array $attributes
     * @return HTMLElement
     */
    public function insertInputPassword($name, $id, $value = '', $label = '', array $attributes = array())
    {
        $container = new HTMLElementImpl('div', array('class' => 'password'));
        $input = new HTMLElementImpl('input', array('type' => 'password', 'name' => $name, 'value' => $value, 'id' => $id));
        $this->insertAttributesNot($input, $attributes, array('type', 'name', 'value', 'id'));
        $labelElement = new HTMLElementImpl('label', array('for' => $id));
        $labelElement->insertString($label);
        $container->insertHTMLElement($labelElement);
        $container->insertHTMLElement($input);
        $this->formElement->insertHTMLElement($container);
        return $container;
    }

    /**
     * Will insert an textarea
     * @param string $value
     * @param string $name
     * @param string $id
     * @param string $label
     * @param array $attributes
     * @return HTMLElement
     */
    public function insertTextArea($name, $id, $value = '', $label = '', array $attributes = array())
    {
        $container = new HTMLElementImpl('div', array('class' => 'textarea'));
        $input = new HTMLElementImpl('textarea', array('name' => $name, 'id' => $id,'rows'=>10,'cols'=>4));
        $input->insertString($value);
        $this->insertAttributesNot($input, $attributes, array('name', 'id'));
        $labelElement = new HTMLElementImpl('label', array('for' => $id));
        $labelElement->insertString($label);
        $container->insertHTMLElement($labelElement);
        $container->insertHTMLElement($input);
        $this->formElement->insertHTMLElement($container);
        return $container;
    }

    private function insertAttributesNot(HTMLElement $element, array $attributes, array $exceptions)
    {
        foreach ($attributes as $attribute => $value) {
            if (!in_array($attribute, $exceptions)) {
                $element->setAttributes($attribute, $value);
            }
        }
    }

    /**
     * @param string $name
     * @param string $id
     * @param string $label
     * @param null $select
     * @param array $attributes
     * @return HTMLSelectElement
     */
    public function insertSelect($name, $id, $label = '',&$select = null, array $attributes = array())
    {
        $container = new HTMLElementImpl('div', array('class' => 'select'));
        $select = new HTMLSelectElementImpl($name,$id);
        $labelElement = new HTMLElementImpl('label', array('for' => $id));
        $labelElement->insertString($label);
        $container->insertHTMLElement($labelElement);
        $container->insertHTMLElement($select);
        $this->formElement->insertHTMLElement($container);
        return $container;
    }

    /**
     * @param string $name
     * @param string $id
     * @param string $value
     * @param string $label
     * @param array $attributes
     * @return HTMLElement
     */
    public function insertCheckbox($name, $id, $value = '', $label = '', array $attributes = array())
    {
        $container = new HTMLElementImpl('div', array('class' => 'checkbox'));
        $input = new HTMLElementImpl('input', array_merge(array('type' => 'checkbox', 'name' => $name, 'value' => $value, 'id' => $id),$attributes));
        $labelElement = new HTMLElementImpl('label', array('for' => $id));
        $labelElement->insertString($label);
        $container->insertHTMLElement($labelElement);
        $container->insertHTMLElement($input);
        $this->formElement->insertHTMLElement($container);
        return $container;
    }

    /**
     * @param string $name
     * @param string $id
     * @param string $value
     * @param string $label
     * @param array $attributes
     * @return HTMLElement
     */
    public function insertRadioButton($name, $id, $value = '', $label = '', array $attributes = array())
    {
        $container = new HTMLElementImpl('div', array('class' => 'radio'));
        $input = new HTMLElementImpl('input', array_merge(array('type' => 'radio', 'name' => $name, 'value' => $value, 'id' => $id),$attributes));
        $labelElement = new HTMLElementImpl('label', array('for' => $id));
        $labelElement->insertString($label);
        $container->insertHTMLElement($labelElement);
        $container->insertHTMLElement($input);
        $this->formElement->insertHTMLElement($container);
        return $container;
    }
}
