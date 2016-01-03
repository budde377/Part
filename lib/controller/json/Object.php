<?php
namespace ChristianBudde\Part\controller\json;

/**
 * User: budde
 * Date: 22/01/13
 * Time: 12:39
 */
interface Object extends Element
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
