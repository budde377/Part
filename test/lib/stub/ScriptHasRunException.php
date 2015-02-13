<?php
namespace ChristianBudde\Part\test\stub;
use Exception;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/28/12
 * Time: 3:03 PM
 * To change this template use File | Settings | File Templates.
 */
class ScriptHasRunException extends Exception
{

    private $name;
    private $args;

    /**
     * @param string $name
     * @param array | null $args
     */

    public function __construct($name, $args)
    {
        $this->name = $name;
        $this->args = $args;
        parent::__construct('The script has run with name:"' . $name . '" and "' . serialize($args) . '"');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array|null
     */
    public function getArgs()
    {
        return $this->args;
    }

}
