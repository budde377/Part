<?php
namespace ChristianBudde\cbweb\exception;

use Exception;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/15/12
 * Time: 8:49 AM
 */
class MalformedParameterException extends Exception
{


    private $expectedType;
    private $parameterNumber;

    /**
     * @param string $expectedType
     * @param int $parameterNumber
     */

    public function __construct($expectedType, $parameterNumber)
    {
        $this->expectedType = $expectedType;
        $this->parameterNumber = $parameterNumber;

        parent::__construct("MalformedParameterException: Did expect parameter #$parameterNumber to be of type $expectedType");
    }

    /**
     * @return int
     */
    public function getParameterNumber()
    {
        return $this->parameterNumber;
    }

    /**
     * @return string
     */
    public function getExpectedType()
    {
        return $this->expectedType;
    }


}
