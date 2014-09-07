<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/30/14
 * Time: 5:12 PM
 */

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/30/14
 * Time: 5:08 PM
 */
interface FunctionStringParser
{
    /**
     * @param $input
     * @param  $result
     * @return bool
     */
    public function parseProgram($input, &$result);

    /**
     * @param $input
     * @param  $result
     * @return bool
     */
    public function parseCompositeFunctionCall($input, &$result);


    /**
     * @param $input
     * @param  $result
     * @return bool
     */
    public function parseCompositeFunction($input, &$result);

    /**
     * @param $input
     * @param  $result
     * @return bool
     */
    public function parseFunctionChain($input, &$result);

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseArrayList($input, &$result);

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseOctal($input, &$result);

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseBinary($input, &$result);

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseNumeric($input, &$result);

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseArgumentList($input, &$result);

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseName($input, &$result);

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseInteger($input, &$result);

    /**
     * @param $input
     * @param  $result
     * @return bool
     */
    public function parseTarget($input, &$result);

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseDoubleNumber($input, &$result);

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseArray($input, &$result);

    /**
     * <program>                    = <composite_function_call> | <function_call>
     *
     * <composite_function_call>    = <target><composite_function>
     * <composite_function>         = [..<function_chain>]+
     * <function_chain>             = <function_chain>.<function> | <function>
     *
     * <function_call>              = <target>.<function> | <target>\[<scalar>\]
     * <function>                   = <name>([<arg>, ...])
     * <target>                     = <function_call> | <name>
     * <arg>                        = <scalar> | <array> | <program>
     * <array>                      = \[ <array_index>, ... \]
     * <array_index>                = <scalar> => <arg> | <arg>
     * <scalar>                     = true | false | null | <num> | *string*
     * <num>                        = [+-]? <integer> | <float>
     * <integer>                    = *decimal* | *hexadecimal* | *octal* | *binary*
     * <float>                      = *double_number* | *exp_double_number*
     * @param string $input
     * @return JSONProgram
     */
    public function parseFunctionString($input);

    /**
     * @param $input
     * @param  $result
     * @return bool
     */
    public function parseFunction($input, &$result);

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseBoolNull($input, &$result);

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseString($input, &$result);

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseScalar($input, &$result);

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseArrayListEntry($input, &$result);

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseDecimal($input, &$result);

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseHexadecimal($input, &$result);

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseArgument($input, &$result);

    /**
     * @param $input
     * @param  $result
     * @return bool
     */
    public function parseFunctionCall($input, &$result);

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseExponentDoubleNum($input, &$result);

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseFloat($input, &$result);

    /**
     * @param $input
     * @param  $result
     * @return bool
     */
    public function parseType($input, &$result);
}