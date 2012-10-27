<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/27/12
 * Time: 11:18 AM
 * To change this template use File | Settings | File Templates.
 */
class FunctionNotImplementedException extends Exception
{
    public function __construct($functionName,$className){
        parent::__construct("FunctionNotImplementedException: Function $className->$functionName has not been implemented.");
    }
}
