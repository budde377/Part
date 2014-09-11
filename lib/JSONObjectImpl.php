<?php
namespace ChristianBudde\cbweb;/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 22/01/13
 * Time: 14:57
 * To change this template use File | Settings | File Templates.
 */
class JSONObjectImpl extends JSONElementImpl implements JSONObject
{

    protected $name;
    private $variables = array();

    /**
     * @param String $name
     */
    function __construct($name)
    {
        $this->name = $name;
    }



    /**
     * @return array
     */
    public function getAsArray()
    {

        return array('name'=>$this->name,'type'=>'object','variables'=>$this->variables);
    }

    /**
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param String $variableName
     * @param $value
     * @return void
     */
    public function setVariable($variableName, $value)
    {
        if(!$this->validValue($value)){
            return;
        }
        $this->variables[$variableName] = $value;
    }

    /**
     * @param String $variableName
     * @return mixed
     */
    public function getVariable($variableName)
    {
        return isset($this->variables[$variableName])?$this->variables[$variableName]:null;
    }
}
