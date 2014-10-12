<?php
namespace ChristianBudde\cbweb\exception;use Exception;
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/30/12
 * Time: 4:38 PM
 */
class InvalidXMLException extends Exception
{
    private $expectedSchema;
    private $xmlDesc;

    /**
     * @param string $expectedSchema
     * @param string $xmlDesc
     */
    public function __construct($expectedSchema = null, $xmlDesc = null)
    {
        $this->expectedSchema = $expectedSchema;
        $this->xmlDesc = $xmlDesc;
        parent::__construct("InvalidXMLException: XML".($xmlDesc!=null?" ($xmlDesc)":"")." was not valid".($expectedSchema!=null?" according to schema: $expectedSchema":""));
    }

    /**
     * @return string
     */

    public function getExpectedSchema()
    {
        return $this->expectedSchema;
    }

    /**
     * @return string XML description
     */
    public function getXmlDesc()
    {
        return $this->xmlDesc;
    }

}
