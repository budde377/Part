<?php
namespace ChristianBudde\Part\view\html;
use ChristianBudde\Part\exception\MalformedParameterException;


/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 25/07/12
 * Time: 15:34
 */
class FormElementImpl implements FormElement
{

    private $formElement;
    /** @var $notion Element | null*/
    private $notion;

    public function __construct($method = FormElement::FORM_METHOD_GET, $action = "#")
    {
        $this->formElement = new ElementImpl('form', array('action' => $action, 'method' => $method));
        $this->notion = new ElementImpl('span');
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
     * @param Element $element
     * @return Element
     */
    public function insertHTMLElement(Element $element)
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
     * @throws \ChristianBudde\Part\exception\MalformedParameterException
     * @return void
     */
    public function setMethod($method = FormElementImpl::FORM_METHOD_GET)
    {
        if ($method != FormElement::FORM_METHOD_GET && $method != FormElement::FORM_METHOD_POST) {
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
    public function setNotion($notion, $type = FormElementImpl::NOTION_TYPE_INFORMATION)
    {
        if ($type != FormElement::NOTION_TYPE_ERROR && $type != FormElement::NOTION_TYPE_INFORMATION &&
            $type != FormElement::NOTION_TYPE_SUCCESS
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
     * @return Element
     */
    public function insertInputText($name, $id, $value = '', $label = '', array $attributes = array())
    {
        $container = new ElementImpl('div', array('class' => 'text'));
        $input = new ElementImpl('input', array('type' => 'text', 'name' => $name, 'value' => $value, 'id' => $id));
        $this->insertAttributesNot($input, $attributes, array('type', 'name', 'value', 'id'));
        $labelElement = new ElementImpl('label', array('for' => $id));
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
     * @return Element
     */
    public function insertInputHidden($value, $name, array $attributes = array())
    {
        $input = new ElementImpl('input', array('type' => 'hidden', 'value' => $value, 'name' => $name));
        $this->insertAttributesNot($input, $attributes, array('type', 'value', 'name'));
        $this->formElement->insertString($input);
        return $input;
    }


    /**
     * Will insert an input submit
     * @param string $value
     * @param array $attributes
     * @return Element
     */
    public function insertInputSubmit($value, array $attributes = array())
    {
        $input = new ElementImpl('input', array('value' => $value, 'type' => 'submit'));
        $this->insertAttributesNot($input, $attributes, array('value', 'name', 'type'));
        $container = new ElementImpl('div',array('class'=>'submit'));
        $container->insertHTMLElement($input);
        $this->formElement->insertHTMLElement($container);
        return $container;
    }

    /**
     * @param string $name
     * @param string $id
     * @param string $label
     * @param array $attributes
     * @return Element
     */
    public function insertInputFileUpload($name, $id, $label = '', array $attributes = array())
    {
        $container = new ElementImpl('div', array('class' => 'fileUpload'));
        $input = new ElementImpl('input', array('type' => 'file', 'id' => $id,'name'=>$name));
        $this->insertAttributesNot($input, $attributes, array('type', 'id'));
        $labelElement = new ElementImpl('label', array('for' => $id));
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
     * @return Element
     */
    public function insertInputPassword($name, $id, $value = '', $label = '', array $attributes = array())
    {
        $container = new ElementImpl('div', array('class' => 'password'));
        $input = new ElementImpl('input', array('type' => 'password', 'name' => $name, 'value' => $value, 'id' => $id));
        $this->insertAttributesNot($input, $attributes, array('type', 'name', 'value', 'id'));
        $labelElement = new ElementImpl('label', array('for' => $id));
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
     * @return Element
     */
    public function insertTextArea($name, $id, $value = '', $label = '', array $attributes = array())
    {
        $container = new ElementImpl('div', array('class' => 'textarea'));
        $input = new ElementImpl('textarea', array('name' => $name, 'id' => $id,'rows'=>10,'cols'=>4));
        $input->insertString($value);
        $this->insertAttributesNot($input, $attributes, array('name', 'id'));
        $labelElement = new ElementImpl('label', array('for' => $id));
        $labelElement->insertString($label);
        $container->insertHTMLElement($labelElement);
        $container->insertHTMLElement($input);
        $this->formElement->insertHTMLElement($container);
        return $container;
    }

    private function insertAttributesNot(Element $element, array $attributes, array $exceptions)
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
     * @return SelectElement
     */
    public function insertSelect($name, $id, $label = '',&$select = null, array $attributes = array())
    {
        $container = new ElementImpl('div', array('class' => 'select'));
        $select = new SelectElementImpl($name,$id);
        $labelElement = new ElementImpl('label', array('for' => $id));
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
     * @return Element
     */
    public function insertCheckbox($name, $id, $value = '', $label = '', array $attributes = array())
    {
        $container = new ElementImpl('div', array('class' => 'checkbox'));
        $input = new ElementImpl('input', array_merge(array('type' => 'checkbox', 'name' => $name, 'value' => $value, 'id' => $id),$attributes));
        $labelElement = new ElementImpl('label', array('for' => $id));
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
     * @return Element
     */
    public function insertRadioButton($name, $id, $value = '', $label = '', array $attributes = array())
    {
        $container = new ElementImpl('div', array('class' => 'radio'));
        $input = new ElementImpl('input', array_merge(array('type' => 'radio', 'name' => $name, 'value' => $value, 'id' => $id),$attributes));
        $labelElement = new ElementImpl('label', array('for' => $id));
        $labelElement->insertString($label);
        $container->insertHTMLElement($labelElement);
        $container->insertHTMLElement($input);
        $this->formElement->insertHTMLElement($container);
        return $container;
    }
}
