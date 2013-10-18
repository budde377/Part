<?php
require_once dirname(__FILE__).'/HTMLElement.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 19/01/13
 * Time: 17:01
 */
interface HTMLOptionElement extends HTMLElement
{

    /**
     * Set selected
     * @param bool $selected
     * @return void
     */
    public function setSelected($selected);

}
