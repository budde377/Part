<?php
require_once dirname(__FILE__) . '/../_interface/XHTMLFormElement.php';
require_once dirname(__FILE__) . '/HTMLElementImpl.php';
require_once dirname(__FILE__) . '/XHTMLSelectElementImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 25/07/12
 * Time: 15:34
 */
class HTMLFormElementImpl implements XHTMLFormElement
{

    private $formElement;
    /** @var $notion XHTMLElement | null*/
    private $notion;

    public function __construct($method = XHTMLFormElement::FORM_METHOD_GET, $action = "#")
    {
        $this->formElement = new HTMLElementImpl('form', array('action' => $action, 'method' => $method));
        $this->notion = new HTMLElementImpl('div');
        $this->formElement->insertXHTMLElement($this->notion);
    }

    /**
     * @return string
     */
    public function getXHTMLString()
    {

        return $this->formElement->getXHTMLString();
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
     * @param XHTMLElement $element
     * @return XHTMLElement
     */
    public function insertXHTMLElement(XHTMLElement $element)
    {
        $this->formElement->insertXHTMLElement($element);
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
     * @throws MalformedParameterException
     * @return void
     */
    public function setMethod($method = HTMLFormElementImpl::FORM_METHOD_GET)
    {
        if ($method != XHTMLFormElement::FORM_METHOD_GET && $method != XHTMLFormElement::FORM_METHOD_POST) {
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
        if ($type != XHTMLFormElement::NOTION_TYPE_ERROR && $type != XHTMLFormElement::NOTION_TYPE_INFORMATION &&
            $type != XHTMLFormElement::NOTION_TYPE_SUCCESS
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
     * @return XHTMLElement
     */
    public function insertInputText($name, $id, $value = '', $label = '', array $attributes = array())
    {
        $container = new HTMLElementImpl('div', array('class' => 'text'));
        $input = new HTMLElementImpl('input', array('type' => 'text', 'name' => $name, 'value' => $value, 'id' => $id));
        $this->insertAttributesNot($input, $attributes, array('type', 'name', 'value', 'id'));
        $labelElement = new HTMLElementImpl('label', array('for' => $id));
        $labelElement->insertString($label);
        $container->insertXHTMLElement($labelElement);
        $container->insertXHTMLElement($input);
        $this->formElement->insertXHTMLElement($container);
        return $container;
    }


    /**
     * @param string $value
     * @param string $name
     * @param array $attributes
     * @return XHTMLElement
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
     * @return XHTMLElement
     */
    public function insertInputSubmit($value, array $attributes = array())
    {
        $input = new HTMLElementImpl('input', array('value' => $value, 'type' => 'submit'));
        $this->insertAttributesNot($input, $attributes, array('value', 'name', 'type'));
        $container = new HTMLElementImpl('div',array('class'=>'submit'));
        $container->insertXHTMLElement($input);
        $this->formElement->insertXHTMLElement($container);
        return $container;
    }

    /**
     * @param string $name
     * @param string $id
     * @param string $label
     * @param array $attributes
     * @return XHTMLElement
     */
    public function insertInputFileUpload($name, $id, $label = '', array $attributes = array())
    {
        $container = new HTMLElementImpl('div', array('class' => 'fileUpload'));
        $input = new HTMLElementImpl('input', array('type' => 'file', 'id' => $id,'name'=>$name));
        $this->insertAttributesNot($input, $attributes, array('type', 'id'));
        $labelElement = new HTMLElementImpl('label', array('for' => $id));
        $labelElement->insertString($label);
        $container->insertXHTMLElement($labelElement);
        $container->insertXHTMLElement($input);
        $this->formElement->insertXHTMLElement($container);
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
     * @return XHTMLElement
     */
    public function insertInputPassword($name, $id, $value = '', $label = '', array $attributes = array())
    {
        $container = new HTMLElementImpl('div', array('class' => 'password'));
        $input = new HTMLElementImpl('input', array('type' => 'password', 'name' => $name, 'value' => $value, 'id' => $id));
        $this->insertAttributesNot($input, $attributes, array('type', 'name', 'value', 'id'));
        $labelElement = new HTMLElementImpl('label', array('for' => $id));
        $labelElement->insertString($label);
        $container->insertXHTMLElement($labelElement);
        $container->insertXHTMLElement($input);
        $this->formElement->insertXHTMLElement($container);
        return $container;
    }

    /**
     * Will insert an textarea
     * @param string $value
     * @param string $name
     * @param string $id
     * @param string $label
     * @param array $attributes
     * @return XHTMLElement
     */
    public function insertTextArea($name, $id, $value = '', $label = '', array $attributes = array())
    {
        $container = new HTMLElementImpl('div', array('class' => 'textarea'));
        $input = new HTMLElementImpl('textarea', array('name' => $name, 'id' => $id,'rows'=>10,'cols'=>4));
        $input->insertString($value);
        $this->insertAttributesNot($input, $attributes, array('name', 'id'));
        $labelElement = new HTMLElementImpl('label', array('for' => $id));
        $labelElement->insertString($label);
        $container->insertXHTMLElement($labelElement);
        $container->insertXHTMLElement($input);
        $this->formElement->insertXHTMLElement($container);
        return $container;
    }

    private function insertAttributesNot(XHTMLElement $element, array $attributes, array $exceptions)
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
     * @return XHTMLSelectElement
     */
    public function insertSelect($name, $id, $label = '',&$select = null, array $attributes = array())
    {
        $container = new HTMLElementImpl('div', array('class' => 'select'));
        $select = new XHTMLSelectElementImpl($name,$id);
        $labelElement = new HTMLElementImpl('label', array('for' => $id));
        $labelElement->insertString($label);
        $container->insertXHTMLElement($labelElement);
        $container->insertXHTMLElement($select);
        $this->formElement->insertXHTMLElement($container);
        return $container;
    }

    /**
     * @param string $name
     * @param string $id
     * @param string $value
     * @param string $label
     * @param array $attributes
     * @return XHTMLElement
     */
    public function insertCheckbox($name, $id, $value = '', $label = '', array $attributes = array())
    {
        $container = new HTMLElementImpl('div', array('class' => 'checkbox'));
        $input = new HTMLElementImpl('input', array_merge(array('type' => 'checkbox', 'name' => $name, 'value' => $value, 'id' => $id),$attributes));
        $labelElement = new HTMLElementImpl('label', array('for' => $id));
        $labelElement->insertString($label);
        $container->insertXHTMLElement($labelElement);
        $container->insertXHTMLElement($input);
        $this->formElement->insertXHTMLElement($container);
        return $container;
    }

    /**
     * @param string $name
     * @param string $id
     * @param string $value
     * @param string $label
     * @param array $attributes
     * @return XHTMLElement
     */
    public function insertRadioButton($name, $id, $value = '', $label = '', array $attributes = array())
    {
        $container = new HTMLElementImpl('div', array('class' => 'radio'));
        $input = new HTMLElementImpl('input', array_merge(array('type' => 'radio', 'name' => $name, 'value' => $value, 'id' => $id),$attributes));
        $labelElement = new HTMLElementImpl('label', array('for' => $id));
        $labelElement->insertString($label);
        $container->insertXHTMLElement($labelElement);
        $container->insertXHTMLElement($input);
        $this->formElement->insertXHTMLElement($container);
        return $container;
    }
}
