<?php
namespace ChristianBudde\cbweb\controller\function_string;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/30/14
 * Time: 5:12 PM
 */
use ChristianBudde\cbweb\controller\function_string\ast\Program;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/30/14
 * Time: 5:08 PM
 */
interface Parser
{

    /**
     * @param array $tokens An assoc. array containing *match* and *token*
     * @return Program
     */
    public function parse(array $tokens);

    /**
     * @param string $input
     * @return Program
     */
    public function parseString($input);
    /**
     * @param array $tokens An assoc. array containing *match* and *token*
     * @return Program
     */
    public function parseArray(array $tokens);

    /**
     * @param string $input
     * @return Program
     */
    public function parseArrayString($input);

}