<?php
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
    public function __construct($expectedSchema, $xmlDesc)
    {
        $this->expectedSchema = $expectedSchema;
        $this->xmlDesc = $xmlDesc;
        parent::__construct("InvalidXMLException: XML ($xmlDesc) was not valid according to schema: $expectedSchema");
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
