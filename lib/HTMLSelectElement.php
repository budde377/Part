<?php
namespace ChristianBudde\cbweb;/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 31/08/12
 * Time: 21:22
 */
interface HTMLSelectElement extends HTMLElement
{
    /**
     * @abstract
     * @param string $text
     * @param string $value
     * @param string $group_id
     * @param array $attributes
     * @return HTMLOptionElement
     */
    public function insertOption($text, $value, $group_id = null, array $attributes = array());

    /**
     * @abstract
     * @param $id
     * @param string $label
     * @return HTMLElement
     */
    public function insertOptionGroup($id, $label);

    /**
     * @abstract
     * @param boolean $multiple
     * @return mixed
     */
    public function setMultiple($multiple);

    /**
     * @abstract
     * @param string $name
     * @return mixed
     */
    public function setName($name);

    /**
     * @abstract
     * @param int $size
     * @return void
     */
    public function setSize($size);

    /**
     * @abstract
     * @param boolean $disabled
     * @return void
     */
    public function setDisabled($disabled);

    /**
     * @abstract
     * @param string $id
     * @return mixed
     */
    public function setId($id);
}
