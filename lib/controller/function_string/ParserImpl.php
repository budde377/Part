<?php
namespace ChristianBudde\Part\controller\function_string;

use ChristianBudde\Part\controller\function_string\ast\AArray;
use ChristianBudde\Part\controller\function_string\ast\Argument;
use ChristianBudde\Part\controller\function_string\ast\ArgumentsImpl;
use ChristianBudde\Part\controller\function_string\ast\ArrayAccessFunctionImpl;
use ChristianBudde\Part\controller\function_string\ast\ArrayEntriesImpl;
use ChristianBudde\Part\controller\function_string\ast\ArrayEntry;
use ChristianBudde\Part\controller\function_string\ast\BinaryUnsignedNumScalarImpl;
use ChristianBudde\Part\controller\function_string\ast\BoolScalarImpl;
use ChristianBudde\Part\controller\function_string\ast\CompositeFunction;
use ChristianBudde\Part\controller\function_string\ast\CompositeFunctionProgramImpl;
use ChristianBudde\Part\controller\function_string\ast\CompositeFunctionsImpl;
use ChristianBudde\Part\controller\function_string\ast\DecimalUnsignedNumScalarImpl;
use ChristianBudde\Part\controller\function_string\ast\DoubleQuotedStringScalarImpl;
use ChristianBudde\Part\controller\function_string\ast\DoubleUnsignedNumScalarImpl;
use ChristianBudde\Part\controller\function_string\ast\EmptyArrayImpl;
use ChristianBudde\Part\controller\function_string\ast\ExpDoubleUnsignedNumScalarImpl;
use ChristianBudde\Part\controller\function_string\ast\FunctionChain;
use ChristianBudde\Part\controller\function_string\ast\FunctionChainCompositeFunctionProgramImpl;
use ChristianBudde\Part\controller\function_string\ast\FunctionChainProgramImpl;
use ChristianBudde\Part\controller\function_string\ast\FunctionChainsImpl;
use ChristianBudde\Part\controller\function_string\ast\HexadecimalUnsignedNumScalarImpl;
use ChristianBudde\Part\controller\function_string\ast\NamedArgument;
use ChristianBudde\Part\controller\function_string\ast\NamedArgumentImpl;
use ChristianBudde\Part\controller\function_string\ast\NamedArgumentsImpl;
use ChristianBudde\Part\controller\function_string\ast\NamedArrayEntriesImpl;
use ChristianBudde\Part\controller\function_string\ast\NamedArrayEntry;
use ChristianBudde\Part\controller\function_string\ast\NamedArrayEntryImpl;
use ChristianBudde\Part\controller\function_string\ast\NamedFunctionImpl;
use ChristianBudde\Part\controller\function_string\ast\NameImpl;
use ChristianBudde\Part\controller\function_string\ast\NameNotStartingWithUnderscore;
use ChristianBudde\Part\controller\function_string\ast\NameNotStartingWithUnderscoreImpl;
use ChristianBudde\Part\controller\function_string\ast\NoArgumentNamedFunctionImpl;
use ChristianBudde\Part\controller\function_string\ast\NonEmptyArrayImpl;
use ChristianBudde\Part\controller\function_string\ast\NullScalarImpl;
use ChristianBudde\Part\controller\function_string\ast\NumScalar;
use ChristianBudde\Part\controller\function_string\ast\OctalUnsignedNumScalarImpl;
use ChristianBudde\Part\controller\function_string\ast\Program;
use ChristianBudde\Part\controller\function_string\ast\Scalar;
use ChristianBudde\Part\controller\function_string\ast\ScalarArrayProgram;
use ChristianBudde\Part\controller\function_string\ast\SignedNumScalarImpl;
use ChristianBudde\Part\controller\function_string\ast\SingleQuotedStringScalarImpl;
use ChristianBudde\Part\controller\function_string\ast\StringScalar;
use ChristianBudde\Part\controller\function_string\ast\Type;
use ChristianBudde\Part\controller\function_string\ast\TypesImpl;
use ChristianBudde\Part\controller\function_string\ast\UnsignedNumScalar;
use Exception;


/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/30/14
 * Time: 5:08 PM
 */
class ParserImpl implements Parser
{

    private $tokens;
    private $num_tokens;
    private $look_ahead;
    private $look_ahead_2;
    private $pointer;

    /**
     * @param $x
     * @throws Exception
     * @return string
     */
    private function match($x)
    {
        if ($this->look_ahead == $x || (is_array($x) && in_array($this->look_ahead, $x))) {
            return $this->consume();
        } else {
            $s = is_array($x) ? implode(" or ", $x) : $x;
            throw new Exception("Expected token $s got {$this->look_ahead}");
        }
    }

    /**
     * @return string
     */
    private function consume()
    {
        $this->pointer += 1;
        $this->look_ahead = $this->pointer >= $this->num_tokens ? null : $this->tokens[$this->pointer]['token'];
        $this->look_ahead_2 = $this->pointer + 1 >= $this->num_tokens ? null : $this->tokens[$this->pointer + 1]['token'];
        return $this->tokens[$this->pointer - 1]['match'];
    }


    /**
     * @param array $tokens An assoc. array containing *match* and *token*
     * @return Program
     */
    public function parse(array $tokens)
    {
        return $this->parseExpect($tokens, function () {
            return $this->expectProgram();
        });
    }


    private function parseExpect(array $tokens, callable $expect)
    {
        $this->tokens = [];
        $i = 0;
        foreach ($tokens as $t) {
            if ($t['token'] != Lexer::T_WHITESPACE) {
                $this->tokens[] = $t;
                $i += 1;
            }
        }
        $this->num_tokens = $i;

        $this->pointer = 0;
        $this->look_ahead = $this->tokens[0]['token'];
        $this->look_ahead_2 = $this->tokens[1]['token'];
        try {
            $result = $expect();
            $this->match(Lexer::T_EOF);
        } catch (Exception $e) {
            return null;
        }
        return $result;
    }

    /**
     * @param string $input
     * @return Program
     */
    public function parseString($input)
    {
        return $this->parse(LexerImpl::lex($input));
    }

    /**
     * @param array $tokens An assoc. array containing *match* and *token*
     * @return array
     */
    public function parseArray(array $tokens)
    {
        return $this->parseExpect($tokens, function () {
            return $this->expectArray()->toArray()[0];
        });
    }

    /**
     * @param string $input
     * @return Program
     */
    public function parseArrayString($input)
    {
        return $this->parseArray(LexerImpl::lex($input));
    }

    /**
     * @return Program
     */
    private function expectProgram()
    {
        $t = $this->expectType();
        $fc = $cf = null;
        if ($this->look_ahead == Lexer::T_L_BRACKET || ($this->look_ahead == Lexer::T_DOT && $this->look_ahead_2 != Lexer::T_DOT && $this->look_ahead_2 != Lexer::T_L_BRACKET)) {
            $fc = $this->expectFunctionChain();

        }

        if ($this->look_ahead == Lexer::T_DOT && ($this->look_ahead_2 == Lexer::T_DOT || $this->look_ahead_2 == Lexer::T_L_BRACKET)) {
            $cf = $this->expectCompositeFunction();
        }

        if ($fc == null && $cf == null) {
            $this->match([Lexer::T_L_BRACKET, Lexer::T_DOT]);
        } else if ($fc == null) {
            return new CompositeFunctionProgramImpl($t, $cf);
        } else if ($cf == null) {
            return new FunctionChainProgramImpl($t, $fc);
        } else {
            return new FunctionChainCompositeFunctionProgramImpl($t, $fc, $cf);
        }
        return null;
    }


    /**
     * @throws Exception
     * @return Type
     */
    private function expectType()
    {
        $name = $this->expectName();
        if ($this->look_ahead == Lexer::T_BACKSLASH) {
            $this->match(Lexer::T_BACKSLASH);
            $name = new TypesImpl($name, $this->expectType());
        }
        return $name;
    }

    /**
     * @return FunctionChain
     * @throws Exception
     */
    private function expectFunctionChain()
    {
        if ($this->look_ahead == Lexer::T_DOT) {
            $this->match(Lexer::T_DOT);
            $name = $this->expectName();
            $this->match(Lexer::T_L_PAREN);
            if ($this->look_ahead != Lexer::T_R_PAREN) {
                $func = new NamedFunctionImpl($name, $this->expectArgument());
            } else {
                $func = new NoArgumentNamedFunctionImpl($name);
            }
            $this->match(Lexer::T_R_PAREN);

        } else {
            $this->match(Lexer::T_L_BRACKET);
            $func = new ArrayAccessFunctionImpl($this->expectScalarArrayProgram());
            $this->match(Lexer::T_R_BRACKET);
        }

        if ($this->look_ahead == Lexer::T_L_BRACKET || ($this->look_ahead == Lexer::T_DOT && $this->look_ahead_2 != Lexer::T_DOT && $this->look_ahead_2 != Lexer::T_L_BRACKET)) {
            $func = new FunctionChainsImpl($func, $this->expectFunctionChain());

        }

        return $func;

    }

    /**
     * @return CompositeFunction
     * @throws Exception
     */
    private function expectCompositeFunction()
    {
        $this->match(Lexer::T_DOT);
        $func = $this->expectFunctionChain();
        if ($this->look_ahead == Lexer::T_DOT) {
            $func = new CompositeFunctionsImpl($func, $this->expectCompositeFunction());
        }
        return $func;
    }

    /**
     * @throws Exception
     * @return NameImpl
     */
    private function expectName()
    {
        if ($this->look_ahead == Lexer::T_NAME_NOT_STARTING_WITH_UNDERSCORE) {
            return $this->expectNameNotStartingWithUnderscore();
        }
        return new NameImpl($this->match(Lexer::T_NAME));
    }

    /**
     * @return Argument
     * @throws Exception
     */
    private function expectArgument()
    {
        if ($this->look_ahead == Lexer::T_NAME_NOT_STARTING_WITH_UNDERSCORE && $this->look_ahead_2 == Lexer::T_COLON) {
            return $this->expectNamedArgument();
        }

        $sap = $this->expectScalarArrayProgram();
        if ($this->look_ahead == Lexer::T_COMMA) {
            $this->match(Lexer::T_COMMA);
            $sap = new ArgumentsImpl($sap, $this->expectArgument());
        }

        return $sap;
    }

    /**
     * @return ScalarArrayProgram
     */
    private function expectScalarArrayProgram()
    {
        switch ($this->look_ahead) {
            case Lexer::T_L_BRACKET:
                return $this->expectArray();
                break;
            case Lexer::T_NAME:
            case Lexer::T_NAME_NOT_STARTING_WITH_UNDERSCORE:
                return $this->expectProgram();
                break;
            default:
                return $this->expectScalar();
        }
    }

    /**
     * @return AArray
     * @throws Exception
     */
    private function expectArray()
    {
        $this->match(Lexer::T_L_BRACKET);
        if ($this->look_ahead != Lexer::T_R_BRACKET) {
            $a = new NonEmptyArrayImpl($this->expectArrayEntry());
        } else {
            $a = new EmptyArrayImpl();
        }
        $this->match(Lexer::T_R_BRACKET);
        return $a;
    }

    /**
     * @return Scalar
     */
    private function expectScalar()
    {
        switch ($this->look_ahead) {
            case Lexer::T_DOUBLE_QUOTED_STRING:
            case Lexer::T_SINGLE_QUOTED_STRING:
                return $this->expectString();
                break;
            case Lexer::T_NULL:
                return $this->expectNull();
                break;
            case Lexer::T_BOOL:
                return $this->expectBool();
                break;
            default:
                return $this->expectNum();
        }
    }

    /**
     * @return StringScalar
     * @throws Exception
     */
    private function expectString()
    {
        if ($this->look_ahead == Lexer::T_SINGLE_QUOTED_STRING) {
            return new SingleQuotedStringScalarImpl($this->match(Lexer::T_SINGLE_QUOTED_STRING));


        }


        return new DoubleQuotedStringScalarImpl($this->match(Lexer::T_DOUBLE_QUOTED_STRING));

    }

    /**
     * @return Scalar
     * @throws Exception
     */
    private function expectNull()
    {
        $this->match(Lexer::T_NULL);
        return new NullScalarImpl();
    }

    /**
     * @return Scalar
     * @throws Exception
     */
    private function expectBool()
    {
        return new BoolScalarImpl($this->match(Lexer::T_BOOL));
    }

    /**
     * @return NumScalar
     * @throws Exception
     */
    private function expectNum()
    {
        if ($this->look_ahead == Lexer::T_SIGN) {
            return new SignedNumScalarImpl($this->match(Lexer::T_SIGN), $this->expectUnsignedNum());
        }
        return $this->expectUnsignedNum();
    }

    /**
     * @return UnsignedNumScalar
     * @throws Exception
     */
    private function expectUnsignedNum()
    {
        $l = $this->look_ahead;
        $n = $this->match([Lexer::T_DOUBLE_NUMBER, Lexer::T_EXP_DOUBLE_NUMBER, Lexer::T_DECIMAL, Lexer::T_BINARY, Lexer::T_OCTAL, Lexer::T_HEXADECIMAL]);
        switch ($l) {
            case Lexer::T_DOUBLE_NUMBER:
                return new DoubleUnsignedNumScalarImpl($n);
            case Lexer::T_EXP_DOUBLE_NUMBER:
                return new ExpDoubleUnsignedNumScalarImpl($n);
            case Lexer::T_DECIMAL:
                return new DecimalUnsignedNumScalarImpl($n);
            case Lexer::T_BINARY:
                return new BinaryUnsignedNumScalarImpl($n);
            case Lexer::T_OCTAL:
                return new OctalUnsignedNumScalarImpl($n);
            default:
                return new HexadecimalUnsignedNumScalarImpl($n);

        }

    }

    /**
     * @return ArrayEntry
     * @throws Exception
     */
    private function expectArrayEntry()
    {
        switch ($this->look_ahead) {
            case Lexer::T_NAME:
            case Lexer::T_NAME_NOT_STARTING_WITH_UNDERSCORE:
                $e = $this->expectProgram();
                break;
            case Lexer::T_L_BRACKET:
                $e = $this->expectArray();
                break;
            default:
                $e = $this->expectScalar();
                if ($this->look_ahead == Lexer::T_DOUBLE_ARROW) {
                    $this->match(Lexer::T_DOUBLE_ARROW);
                    $e = new NamedArrayEntryImpl($e, $this->expectScalarArrayProgram());
                }
        }

        if ($this->look_ahead == Lexer::T_COMMA) {
            $this->match(Lexer::T_COMMA);

            if ($e instanceof NamedArrayEntry) {
                $e = new NamedArrayEntriesImpl($e->getName(), $e->getValue(), $this->expectArrayEntry());
            } else {
                $e = new ArrayEntriesImpl($e, $this->expectArrayEntry());
            }
        }
        return $e;
    }

    /**
     * @return NamedArgument
     * @throws Exception
     */
    private function expectNamedArgument()
    {
        $name = $this->expectNameNotStartingWithUnderscore();
        $this->match(Lexer::T_COLON);
        $value = $this->expectScalarArrayProgram();
        if ($this->look_ahead == Lexer::T_COMMA) {
            $this->match(Lexer::T_COMMA);

            return new NamedArgumentsImpl($name, $value, $this->expectNamedArgument());
        }

        return new NamedArgumentImpl($name, $value);

    }

    /**
     * @return NameNotStartingWithUnderscore
     * @throws Exception
     */
    private function expectNameNotStartingWithUnderscore()
    {
        return new NameNotStartingWithUnderscoreImpl($this->match(Lexer::T_NAME_NOT_STARTING_WITH_UNDERSCORE));
    }


}