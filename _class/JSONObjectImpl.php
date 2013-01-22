<?php
require_once dirname(__FILE__).'/../_interface/JSONObject.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 22/01/13
 * Time: 14:57
 * To change this template use File | Settings | File Templates.
 */
class JSONObjectImpl implements JSONObject
{

    private $name;
    private $variables = array();

    /**
     * @param String $name
     */
    function __construct($name)
    {
        $this->name = $name;
    }


    /**
     * @return string
     */
    public function getAsJSONString()
    {
        return json_encode($this->getAsArray());
    }

    /**
     * @return array
     */
    public function getAsArray()
    {
        $variableArray = array();
        foreach($this->variables as $key=>$val){
            if($val instanceof JSONObject){
                $variableArray[$key] = $val->getAsArray();
            } else {
                $variableArray[$key] = $val;
            }
        }
        return array('name'=>$this->name,'type'=>'object','variables'=>$variableArray);
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
        if(!is_scalar($value) && !($value instanceof JSONObject)){
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
