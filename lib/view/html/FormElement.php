<?php
namespace ChristianBudde\Part\view\html;


/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 25/07/12
 * Time: 14:53
 */
interface FormElement extends Element
{
    const FORM_METHOD_GET = 'get';
    const FORM_METHOD_POST = 'post';

    const NOTION_TYPE_ERROR = 'error';
    const NOTION_TYPE_SUCCESS = 'success';
    const NOTION_TYPE_INFORMATION = 'info';

    /**
     * @abstract
     * Sets the form method, must either be post or get
     * if none of those exception will be thrown
     * @param string $method
     * @return void
     */
    public function setMethod($method = FormElementImpl::FORM_METHOD_GET);

    /**
     * @abstract Sets the action of the form, default is #
     * @param string $action
     * @return void
     */
    public function setAction($action);

    /**
     * @abstract
     * Sets the notion, this can be info, error or success.
     * If none of those is given, exception will be thrown.
     * @param string $notion
     * @param string $type
     * @return void
     */
    public function setNotion($notion,$type=FormElementImpl::NOTION_TYPE_INFORMATION);

    /**
     * @abstract
     * Will insert an input text
     * @param string $value
     * @param string $name
     * @param string $id
     * @param string $label
     * @param array $attributes
     * @return Element
     */
    public function insertInputText($name,$id,$value= '',$label='',array $attributes = array());
    //TODO add validation entry?

    /**
     * @abstract
     * Will insert an input password.
     * @param string $value
     * @param string $name
     * @param string $id
     * @param string $label
     * @param array $attributes
     * @return Element
     */
    public function insertInputPassword($name,$id,$value='',$label='',array $attributes = array());

    /**
     * @abstract
     * @param string $value
     * @param string $name
     * @param array $attributes
     * @return Element
     */
    public function insertInputHidden($name,$value,array $attributes = array());

    /**
     * @abstract
     * Will insert an textarea
     * @param string $value
     * @param string $name
     * @param string $id
     * @param string $label
     * @param array $attributes
     * @return Element
     */
    public function insertTextArea($name,$id,$value='',$label='',array $attributes = array());

    /**
     * @abstract
     * Will insert an input submit
     * @param string $value
     * @param array $attributes
     * @return Element
     */
    public function insertInputSubmit($value,array $attributes = array());

    /**
     * @abstract
     * @param string $name
     * @param string $id
     * @param string $label
     * @param array $attributes
     * @return Element
     */
    public function insertInputFileUpload($name,$id,$label='',array $attributes= array());

    /**
     * @abstract
     * @param string $name
     * @param string $id
     * @param string $label
     * @param null $select
     * @param array $attributes
     * @return SelectElement
     */
    public function insertSelect($name,$id,$label = '',&$select = null, array $attributes = array());


    /**
     * @abstract
     * @param string $name
     * @param string $id
     * @param string $value
     * @param string $label
     * @param array $attributes
     * @return Element
     */
    public function insertCheckbox($name,$id,$value = '', $label = '', array $attributes = array());

    /**
     * @abstract
     * @param string $name
     * @param string $id
     * @param string $value
     * @param string $label
     * @param array $attributes
     * @return Element
     */
    public function insertRadioButton($name,$id,$value = '', $label = '', array $attributes = array());
}
