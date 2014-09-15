<?php
namespace ChristianBudde\cbweb\controller\function_string;

use ChristianBudde\cbweb\controller\json\JSONCompositeFunctionImpl;
use ChristianBudde\cbweb\controller\json\JSONFunction;
use ChristianBudde\cbweb\controller\json\JSONFunctionImpl;


use ChristianBudde\cbweb\controller\json\JSONTypeImpl;

use ChristianBudde\cbweb\controller\json\NullJSONTargetImpl;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/30/14
 * Time: 5:08 PM
 */
class FunctionStringParserImpl implements FunctionStringParser
{

    private $lNumPattern;
    private $dNumPattern;

    function __construct()
    {
        $this->lNumPattern = "[0-9]+";
        $this->dNumPattern = "([0-9]*[\.]{$this->lNumPattern})|({$this->lNumPattern}[\.][0-9]*)";
    }


    /**
     * <program>                    = <composite_function_call> | <function_call>
     *
     * <composite_function_call>    = <target><composite_function>
     * <composite_function>         = [..<function_chain>]+
     * <function_chain>             = <function_chain>.<function> | <function>
     *
     * <function_call>              = <target>.<function> | <target>\[<scalar>\]
     * <function>                   = <name>([<arg>, ...])
     * <target>                     = <function_call> | <type>
     * <type>                       = *name w. backslash *
     * <arg>                        = <scalar> | <array> | <program>
     * <array>                      = \[ <array_index>, ... \]
     * <array_index>                = <scalar> => <arg> | <arg>
     * <scalar>                     = true | false | null | <num> | *string*
     * <num>                        = [+-]? <integer> | <float>
     * <integer>                    = *decimal* | *hexadecimal* | *octal* | *binary*
     * <float>                      = *double_number* | *exp_double_number*
     * @param string $input
     * @return \ChristianBudde\cbweb\controller\json\JSONProgram
     */

    public function parseFunctionString($input)
    {
        return $this->parseProgram($input, $result) ? $result : null;
    }


    /**
     * @param $input
     * @param  $result
     * @return bool
     */
    public function parseProgram($input, &$result){
        return $this->parseFunctionCall($input, $result) || $this->parseCompositeFunctionCall($input, $result);
    }

    /**
     * @param $input
     * @param  $result
     * @return bool
     */
    public function parseCompositeFunctionCall($input, &$result)
    {

        preg_match_all('/\.\./', $input, $matches, PREG_OFFSET_CAPTURE);


        foreach ($matches as $match) {
            if (!isset($match[0][1])) {
                continue;
            }
            if ($this->parseTarget(substr($input, 0, $match[0][1]), $resultTarget) &&
                $this->parseCompositeFunction(substr($input, $match[0][1]), $resultFunction)

            ) {
                /** @var $resultTarget \ChristianBudde\cbweb\controller\json\JSONTarget */
                /** @var $resultFunction \ChristianBudde\cbweb\controller\json\JSONFunction */
                $resultFunction->setTarget($resultTarget);
                $result = $resultFunction;
                return true;
            }
        }
        return false;

    }



    /**
     * @param $input
     * @param  $result
     * @return bool
     */
    public function parseCompositeFunction($input, &$result)
    {

        $input = trim($input);
        if(substr($input, 0,2) != ".."){
            return false;
        }

        if($this->parseFunctionChain(substr($input, 2), $resultFunctionChainBaseCase)){
            $result = new JSONCompositeFunctionImpl( new NullJSONTargetImpl());
            $result->prependFunction($resultFunctionChainBaseCase);
            return true;
        }


        $pos = strpos($input, "..",2);

        while ($pos !== false) {
            if ($this->parseCompositeFunction(substr($input, 0 ,$pos), $resultCompositeFunction) && $this->parseFunctionChain(substr($input, $pos+2), $resultFunctionChain)) {
                /** @var $resultCompositeFunction \ChristianBudde\cbweb\controller\json\JSONCompositeFunction */
                $resultCompositeFunction->appendFunction($resultFunctionChain);
                $result = $resultCompositeFunction;
                return true;
            }
            $pos = strpos($input, "..", $pos + 2);
        }

        return false;

    }

    /**
     * @param $input
     * @param  $result
     * @return bool
     */
    public function parseFunctionChain($input, &$result)
    {


        if($this->parseFunction($input, $result)){
            return true;
        }

        preg_match_all('/\./', $input, $matches, PREG_OFFSET_CAPTURE);
        array_reverse($matches);

        foreach ($matches[0] as $match) {

            if ($this->parseFunction(substr($input, $match[1] + 1), $resultFunction) &&
                $this->parseFunctionChain(substr($input, 0, $match[1]), $resultFunctionChain)
            ) {
                /** @var $resultTarget \ChristianBudde\cbweb\controller\json\JSONTarget */
                /** @var $resultFunction JSONFunction */
                $resultFunction->setTarget($resultFunctionChain);
                $result = $resultFunction;
                return true;
            }
        }
        return false;
    }

    /**
     * @param $input
     * @param  $result
     * @return bool
     */
    public function parseFunctionCall($input, &$result)
    {
        $input = trim($input);
        if(substr($input, -1,1) == "]"){
            preg_match_all('/\[/', $input, $matches, PREG_OFFSET_CAPTURE);
            foreach ($matches[0] as $match) {

                if ($this->parseScalar(substr($input, $match[1] + 1, -1), $resultScalar) &&
                    $this->parseTarget(substr($input, 0, $match[1]), $resultTarget)
                ) {
                    /** @var $resultTarget \ChristianBudde\cbweb\controller\json\JSONTarget */
                    /** @var $resultFunction \ChristianBudde\cbweb\controller\json\JSONFunction */
                    $result = new JSONFunctionImpl("arrayAccess", $resultTarget);
                    $result->setArg(0, $resultScalar);
                    return true;
                }
            }
        }



        preg_match_all('/\./', $input, $matches, PREG_OFFSET_CAPTURE);
        $matches = array_reverse($matches);

        foreach ($matches[0] as $match) {

            if ($this->parseFunction(substr($input, $match[1] + 1), $resultFunction) &&
                $this->parseTarget(substr($input, 0, $match[1]), $resultTarget)
            ) {
                /** @var $resultTarget \ChristianBudde\cbweb\controller\json\JSONTarget */
                /** @var $resultFunction \ChristianBudde\cbweb\controller\json\JSONFunction */
                $resultFunction->setTarget($resultTarget);
                $result = $resultFunction;
                return true;
            }
        }
        return false;
    }

    /**
     * @param $input
     * @param  $result
     * @return bool
     */
    public function parseFunction($input, &$result)
    {
        $input = trim($input);
        if (preg_match('/^([^(]+)\((.*)\)$/', $input, $match) &&
            $this->parseName($match[1], $resultName)

        ) {
            if ($match[2] == "") {
                $result = new JSONFunctionImpl($resultName, new NullJSONTargetImpl());
                return true;
            }

            if ($this->parseArgumentList($match[2], $resultArgumentList)) {

                $result = new JSONFunctionImpl($resultName, new NullJSONTargetImpl());
                foreach ($resultArgumentList as $key => $arg) {
                    $result->setArg($key, $arg);
                }
                return true;
            }
        }

        return false;
    }

    /**
     * @param $input
     * @param  $result
     * @return bool
     */
    public function parseTarget($input, &$result)
    {
        return $this->parseType($input, $result) || $this->parseFunctionCall($input, $result);
    }

    /**
     * @param $input
     * @param  $result
     * @return bool
     */
    public function parseType($input, &$result)
    {
        if ($this->parseNamespaceName($input, $resultName)) {
            $result = new JSONTypeImpl($resultName);
            return true;
        }
        return false;
    }

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseName($input, &$result)
    {
        $input = trim($input);
        if ($r = preg_match('/^[a-z0-9_]+$/i', $input)) {
            $result = $input;
        }

        return $r == 1;
    }
    /**
     * @param $input
     * @param $result
     * @return bool
     */
    private function parseNamespaceName($input, &$result)
    {
        $input = trim($input);
        if ($r = preg_match('/^[a-z0-9_\\\\]+$/i', $input)) {
            $result = $input;
        }

        return $r == 1;
    }

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseArgumentList($input, &$result)
    {

        if ($this->parseArgument($input, $resultArgumentBaseCase)) {
            $result = [$resultArgumentBaseCase];
            return true;
        }

        $pos = strpos($input, ",");
        while ($pos !== false) {
            if ($this->parseArgument(substr($input, 0, $pos), $resultArgument) && $this->parseArgumentList(substr($input, $pos + 1), $resultArgumentList)) {
                $result = array_merge([$resultArgument], $resultArgumentList);
                return true;
            }
            $pos = strpos($input, ",", $pos + 1);
        }

        return false;

    }

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseArgument($input, &$result)
    {
        return $this->parseScalar($input, $result) || $this->parseArray($input, $result) || $this->parseProgram($input, $result);
    }


    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseScalar($input, &$result)
    {
        return $this->parseBoolNull($input, $result) || $this->parseString($input, $result) || $this->parseNumeric($input, $result);
    }

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseString($input, &$result)
    {
        $input = trim($input);
        $startCharacter = substr($input, 0, 1);
        $endCharacter = substr($input, -1, 1);
        if ($startCharacter != $endCharacter || ($startCharacter != "'" && $startCharacter != '"')) {
            return false;
        }

        $i = str_replace("\\\\", "x", $input);
        $i = str_replace("\\$startCharacter", "x", $i);

        if (substr($i, 0, 1) != $startCharacter || substr($i, -1, 1) != $startCharacter) {
            return false;
        }

        $i = substr($i, 1, strlen($i) - 2);

        if (strpos($i, $startCharacter) !== false) {
            return false;
        }

        $result = $input;
        if ($startCharacter == '"') {
            $result = preg_replace("|([^\\\\])\\\\n|", "$1\n", $result);
            $result = preg_replace("/([^\\\\])\\\\r/", "$1\r", $result);
            $result = preg_replace("/([^\\\\])\\\\t/", "$1\t", $result);
            $result = preg_replace("/([^\\\\])\\\\v/", "$1\v", $result);
            $result = preg_replace("/([^\\\\])\\\\e/", "$1\e", $result);
            $result = preg_replace("/([^\\\\])\\\\f/", "$1\f", $result);
            $result = preg_replace_callback("/([^\\\\])\\\\([0-7]{1,3})/",
                function ($m) {
                    return $m[1] . chr(octdec($m[2]));
                }, $result);
            $result = preg_replace_callback("/([^\\\\])\\\\x([0-9A-Fa-f]{1,2})/",
                function ($m) {
                    return $m[1] . chr(hexdec($m[2]));
                }, $result);

        }
        $result = preg_replace("/([^\\\\])\\\\$startCharacter/", "$1$startCharacter", $result);
        $result = str_replace("\\\\", "\\", $result);
        $result = substr($result, 1, strlen($result) - 2);

        return true;
    }

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseNumeric($input, &$result)
    {
        $input = trim($input);

        $signString = substr($input, 0, 1);
        $sign = 1;

        if ($isMinus = ($signString == "-")) {
            $sign = -1;
        }

        if ($isMinus || $signString == "+") {
            $input = substr($input, 1);
        }

        if (!$this->parseInteger($input, $result) && !$this->parseFloat($input, $result)) {
            return false;
        }
        $result = $sign * $result;

        return true;
    }

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseFloat($input, &$result)
    {
        return $this->parseDoubleNumber($input, $result) || $this->parseExponentDoubleNum($input, $result);
    }


    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseDoubleNumber($input, &$result)
    {
        $input = trim($input);
        if (preg_match("/^({$this->dNumPattern})$/", $input)) {
            $result = floatval($input);
            return true;
        }

        return false;
    }


    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseExponentDoubleNum($input, &$result)
    {
        $input = trim($input);

        if (preg_match("/^(({$this->lNumPattern}|{$this->dNumPattern})[eE][+-]?{$this->lNumPattern})$/", $input)) {
            $result = floatval($input);
            return true;
        }


        return false;
    }


    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseInteger($input, &$result)
    {

        return $this->parseDecimal($input, $result) ||
        $this->parseHexadecimal($input, $result) ||
        $this->parseOctal($input, $result) ||
        $this->parseBinary($input, $result);
    }

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseDecimal($input, &$result)
    {
        $input = trim($input);
        if ($input == '0' || preg_match('/^[1-9][0-9]*$/', $input)) {
            $result = intval($input);
            return true;
        }

        return false;
    }

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseHexadecimal($input, &$result)
    {
        $input = trim($input);
        if (preg_match('/^0[xX]([0-9a-fA-F]+)$/', $input, $match)) {
            $result = intval($match[1], 16);
            return true;
        }

        return false;
    }

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseOctal($input, &$result)
    {
        $input = trim($input);
        if (preg_match('/^0([0-7]+)$/', $input, $match)) {
            $result = intval($match[1], 8);
            return true;
        }

        return false;
    }

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseBinary($input, &$result)
    {
        $input = trim($input);
        if (preg_match('/^0b([01]+)$/', $input, $match)) {
            $result = intval($match[1], 2);
            return true;
        }

        return false;
    }

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseArray($input, &$result)
    {
        $input = trim($input);
        if (substr($input, 0, 1) != "[" || substr($input, -1, 1) != "]") {
            return false;
        }

        $input = substr($input, 1, strlen($input) - 2);

        if (trim($input) == "") {
            $result = [];
            return true;
        }

        return $this->parseArrayList($input, $result);
    }

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseArrayList($input, &$result)
    {
        if (trim($input) == "") {

            return false;
        }

        if ($this->parseArrayListEntry($input, $result)) {
            return true;
        }

        $pos = strpos($input, ",");
        while ($pos !== false) {
            if ($this->parseArrayListEntry($s1 = substr($input, 0, $pos), $result) && $this->parseArrayList($s2 = substr($input, $pos + 1), $result)) {

                return true;
            }
            $pos = strpos($input, ",", $pos + 1);
        }

        return false;
    }

    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseArrayListEntry($input, &$result)
    {
        if ($this->parseArgument($input, $resultArgument)) {
            if (is_array($result)) {
                $result[] = $resultArgument;
            } else {
                $result = [$resultArgument];
            }
            return true;

        }


        $pos = strpos($input, "=>");
        while ($pos !== false) {
            if ($this->parseScalar(substr($input, 0, $pos), $resultScalar) && $this->parseArgument(substr($input, $pos + 2), $resultArgument)) {
                if (is_array($result)) {
                    $result[$resultScalar] = $resultArgument;
                } else {
                    $result = [$resultScalar => $resultArgument];
                }
                return true;
            }
            $pos = strpos($input, "=>", $pos + 2);
        }


        return false;
    }


    /**
     * @param $input
     * @param $result
     * @return bool
     */
    public function parseBoolNull($input, &$result)
    {
        if (($its = strtolower(trim($input))) == "true") {
            $result = true;
            return true;
        }

        if ($its == "false") {
            $result = false;
            return true;
        }

        if ($its == "null") {
            $result = null;
            return true;
        }

        return false;
    }


} 