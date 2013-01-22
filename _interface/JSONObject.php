<?php
require_once dirname(__FILE__).'/JSONElement.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 22/01/13
 * Time: 12:39
 * To change this template use File | Settings | File Templates.
 */
interface JSONObject extends JSONElement
{
    /**
     * @return String
     */
    public function getName();

    /**
     * @param String $variableName
     * @param $value
     * @return void
     */
    public function setVariable($variableName,$value);

    /**
     * @param String $variableName
     * @return mixed
     */
    public function getVariable($variableName);

}
