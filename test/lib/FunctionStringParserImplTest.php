<?php
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\controller\function_string\ParserImpl;
use ChristianBudde\cbweb\controller\json\CompositeFunctionImpl;
use ChristianBudde\cbweb\controller\json\JSONFunction;
use ChristianBudde\cbweb\controller\json\JSONFunctionImpl;
use ChristianBudde\cbweb\controller\json\NullTargetImpl;
use ChristianBudde\cbweb\controller\json\Type;
use ChristianBudde\cbweb\controller\json\TypeImpl;
use PHPUnit_Framework_TestCase;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/30/14
 * Time: 5:13 PM
 */
class FunctionStringParserImplTest extends PHPUnit_Framework_TestCase
{

    /** @var  ParserImpl */
    private $parser;

    private $validName = "Some_name89";
    private $nullTarget;

    protected function setUp()
    {
        $this->nullTarget = new NullTargetImpl();
        $this->parser = new ParserImpl();
    }


    public function testResultIsNotModifiedOnUnsuccessfulParse()
    {
        $result = $this;
        $input = "ø";
        $this->parser->parseArrayList($input, $result);
        $this->parser->parseOctal($input, $result);
        $this->parser->parseBinary($input, $result);
        $this->parser->parseNumeric($input, $result);
        $this->parser->parseArgumentList($input, $result);
        $this->parser->parseName($input, $result);
        $this->parser->parseInteger($input, $result);
        $this->parser->parseTarget($input, $result);
        $this->parser->parseDoubleNumber($input, $result);
        $this->parser->parseArray($input, $result);
        $this->parser->parseFunctionString($input);
        $this->parser->parseFunction($input, $result);
        $this->parser->parseBoolNull($input, $result);
        $this->parser->parseString($input, $result);
        $this->parser->parseScalar($input, $result);
        $this->parser->parseArrayListEntry($input, $result);
        $this->parser->parseDecimal($input, $result);
        $this->parser->parseHexadecimal($input, $result);
        $this->parser->parseArgument($input, $result);
        $this->parser->parseFunctionCall($input, $result);
        $this->parser->parseExponentDoubleNum($input, $result);
        $this->parser->parseFloat($input, $result);
        $this->parser->parseType($input, $result);

        $this->assertTrue($this === $result);

    }


    public function testParseType()
    {
        $this->assertTrue($this->parser->parseType($this->validName, $result));
        $this->assertInstanceOf("ChristianBudde\\cbweb\\controller\\json\\Type", $result);
        /** @var Type $result */
        $this->assertEquals($this->validName, $result->getTypeString());

        $this->assertTrue($this->parser->parseType($n = "Site\\something", $result));
        $this->assertInstanceOf("ChristianBudde\\cbweb\\controller\\json\\Type", $result);
        /** @var Type $result */
        $this->assertEquals($n, $result->getTypeString());
    }

    public function testParseName()
    {
        $this->assertTrue($this->parser->parseName($this->validName, $result));
        $this->assertEquals($this->validName, $result);
        $this->assertTrue($this->parser->parseName(" " . $this->validName . " ", $result));
        $this->assertEquals($this->validName, $result);
        $this->assertFalse($this->parser->parseName('Some-Invalid Name', $result));
        $this->assertFalse($this->parser->parseName(' ', $result));
        $this->assertTrue($this->parser->parseName('_asd', $result));
        $this->assertTrue($this->parser->parseName('_', $result));
        $this->assertTrue($this->parser->parseName('a', $result));
        $this->assertTrue($this->parser->parseName('a0', $result));
        $this->assertFalse($this->parser->parseName('0asd', $result));
    }


    public function testParseNamespaceName()
    {
        $this->assertTrue($this->parser->parseNamespaceName($this->validName, $result));
        $this->assertEquals($this->validName, $result);
        $this->assertTrue($this->parser->parseNamespaceName(" " . $this->validName . " ", $result));
        $this->assertEquals($this->validName, $result);
        $this->assertFalse($this->parser->parseNamespaceName('Some-Invalid Name', $result));
        $this->assertFalse($this->parser->parseNamespaceName(' ', $result));
        $this->assertTrue($this->parser->parseNamespaceName('_Asd', $result));
        $this->assertTrue($this->parser->parseNamespaceName('_', $result));
        $this->assertTrue($this->parser->parseNamespaceName('a', $result));
        $this->assertTrue($this->parser->parseNamespaceName('A', $result));
        $this->assertTrue($this->parser->parseNamespaceName('a0', $result));
        $this->assertFalse($this->parser->parseNamespaceName('0asd', $result));
        $this->assertFalse($this->parser->parseNamespaceName('\asd', $result));
        $this->assertTrue($this->parser->parseNamespaceName('asd\\asd', $result));
        $this->assertFalse($this->parser->parseNamespaceName('\\', $result));
        $this->assertFalse($this->parser->parseNamespaceName('asd\\', $result));
        $this->assertFalse($this->parser->parseNamespaceName('0', $result));
    }

    public function testParseDoubleNumber()
    {
        $this->assertTrue($this->parser->parseDoubleNumber("1.123", $result));
        $this->assertEquals(1.123, $result);
        $this->assertTrue($this->parser->parseDoubleNumber(" 1.123 ", $result));
        $this->assertEquals(1.123, $result);
        $this->assertTrue($this->parser->parseDoubleNumber(" 00001.123 ", $result));
        $this->assertEquals(1.123, $result);
        $this->assertTrue($this->parser->parseDoubleNumber(" .123 ", $result));
        $this->assertEquals(.123, $result);
        $this->assertTrue($this->parser->parseDoubleNumber(" 1. ", $result));
        $this->assertEquals(1, $result);
        $this->assertFalse($this->parser->parseDoubleNumber(" 1 123 ", $result));
        $this->assertFalse($this->parser->parseDoubleNumber(" 1123e100 ", $result));
        $this->assertFalse($this->parser->parseDoubleNumber("+1123.", $result));
        $this->assertFalse($this->parser->parseDoubleNumber("-1123.", $result));
    }

    public function testParseExponentDoubleNum()
    {
        $this->assertTrue($this->parser->parseExponentDoubleNum("1.123e10", $result));
        $this->assertEquals(1.123e10, $result);
        $this->assertTrue($this->parser->parseExponentDoubleNum(" 1.123e0 ", $result));
        $this->assertEquals(1.123e0, $result);
        $this->assertTrue($this->parser->parseExponentDoubleNum("1E10 ", $result));
        $this->assertEquals(1E10, $result);
        $this->assertTrue($this->parser->parseExponentDoubleNum(" 1.E00011 ", $result));
        $this->assertEquals(1.E00011, $result);
        $this->assertFalse($this->parser->parseExponentDoubleNum(" 1  E 123 ", $result));
        $this->assertFalse($this->parser->parseExponentDoubleNum("+1E123", $result));
        $this->assertFalse($this->parser->parseExponentDoubleNum("-1E123", $result));
        $this->assertFalse($this->parser->parseExponentDoubleNum("-1E123", $result));
    }

    public function testParseNumeric()
    {


        $this->assertTrue($this->parser->parseNumeric("00123", $result));
        $this->assertEquals(0123, $result);

        $this->assertTrue($this->parser->parseNumeric("00129", $result));
        $this->assertEquals(129, $result);

        $this->assertTrue($this->parser->parseNumeric("+1.1", $result));
        $this->assertEquals(+1.1, $result);
        $this->assertTrue($this->parser->parseNumeric("+  1.1", $result));
        $this->assertEquals(+1.1, $result);
        $this->assertTrue($this->parser->parseNumeric("-1.1", $result));
        $this->assertEquals(-1.1, $result);
        $this->assertTrue($this->parser->parseNumeric("-  1.1", $result));
        $this->assertEquals(-1.1, $result);
        $this->assertTrue($this->parser->parseNumeric("1", $result));
        $this->assertEquals(1, $result);
        $this->assertTrue($this->parser->parseNumeric("-1", $result));
        $this->assertEquals(-1, $result);
        $this->assertTrue($this->parser->parseNumeric("+1", $result));
        $this->assertEquals(1, $result);
        $this->assertFalse($this->parser->parseNumeric("--1.1", $result));
        $this->assertFalse($this->parser->parseNumeric("+-1.1", $result));

    }

    public function testParseFloat()
    {
        $this->assertTrue($this->parser->parseFloat("1.123e10", $result));
        $this->assertEquals(1.123e10, $result);
        $this->assertTrue($this->parser->parseFloat("1.123", $result));
        $this->assertEquals(1.123, $result);
        $this->assertTrue($this->parser->parseFloat(".123e123", $result));
        $this->assertEquals(.123e123, $result);
        $this->assertTrue($this->parser->parseFloat(".123e-123", $result));
        $this->assertEquals(.123e-123, $result);
        $this->assertTrue($this->parser->parseFloat("0e-0", $result));
        $this->assertEquals(0, $result);
    }

    public function testParseDecimal()
    {
        $this->assertTrue($this->parser->parseDecimal("123", $result));
        $this->assertEquals(123, $result);
        $this->assertTrue($this->parser->parseDecimal(" 123 ", $result));
        $this->assertEquals(123, $result);
        $this->assertTrue($this->parser->parseDecimal(" 0123 ", $result));
        $this->assertEquals(123, $result);
        $this->assertFalse($this->parser->parseDecimal(" 123.1 ", $result));
        $this->assertFalse($this->parser->parseDecimal(" 123 1 ", $result));
        $this->assertFalse($this->parser->parseDecimal(" 123a1 ", $result));
        $this->assertFalse($this->parser->parseDecimal(" +123 ", $result));
    }

    public function testParseInteger()
    {
        $this->assertTrue($this->parser->parseInteger(" 123 ", $result));
        $this->assertTrue($this->parser->parseInteger(" 0x123 ", $result));
        $this->assertTrue($this->parser->parseInteger(" 0123 ", $result));
        $this->assertTrue($this->parser->parseInteger(" 0b01 ", $result));
        $this->assertFalse($this->parser->parseInteger(" -123 ", $result));
        $this->assertFalse($this->parser->parseInteger(" 123. ", $result));
    }

    public function testParseHexadecimal()
    {
        $this->assertTrue($this->parser->parseHexadecimal("0x123", $result));
        $this->assertEquals(0x123, $result);
        $this->assertTrue($this->parser->parseHexadecimal("0x1234567890ABCDEF", $result));
        $this->assertEquals(0x1234567890ABCDEF, $result);
        $this->assertTrue($this->parser->parseHexadecimal("0X123", $result));
        $this->assertEquals(0X123, $result);
        $this->assertTrue($this->parser->parseHexadecimal(" 0X123 ", $result));
        $this->assertEquals(0X123, $result);
        $this->assertFalse($this->parser->parseHexadecimal(" 0x123.1 ", $result));
        $this->assertFalse($this->parser->parseHexadecimal(" +0x123 ", $result));
        $this->assertFalse($this->parser->parseHexadecimal(" -0x123 ", $result));
        $this->assertFalse($this->parser->parseHexadecimal(" 0x123G ", $result));
        $this->assertFalse($this->parser->parseHexadecimal(" 1 ", $result));

    }

    public function testParseOctal()
    {
        $this->assertTrue($this->parser->parseOctal("01234567", $result));
        $this->assertEquals(01234567, $result);
        $this->assertTrue($this->parser->parseOctal(" 01234567 ", $result));
        $this->assertEquals(01234567, $result);
        $this->assertFalse($this->parser->parseOctal(" 08 ", $result));
        $this->assertFalse($this->parser->parseOctal(" 0 7 ", $result));
        $this->assertFalse($this->parser->parseOctal(" 0x1 ", $result));
        $this->assertFalse($this->parser->parseOctal(" 1 ", $result));
    }

    public function testParseBinary()
    {
        $this->assertTrue($this->parser->parseBinary("0b10", $result));
        $this->assertEquals(0b10, $result);
        $this->assertTrue($this->parser->parseBinary(" 0b01 ", $result));
        $this->assertEquals(0b01, $result);

        $this->assertFalse($this->parser->parseBinary(" 01 ", $result));
        $this->assertFalse($this->parser->parseBinary(" 0 7 ", $result));
        $this->assertFalse($this->parser->parseBinary(" 0x1 ", $result));
        $this->assertFalse($this->parser->parseBinary(" 1 ", $result));

    }

    public function testParseBoolNull()
    {
        $this->assertTrue($this->parser->parseBoolNull("true", $result));
        $this->assertEquals(true, $result);
        $this->assertTrue($this->parser->parseBoolNull("false", $result));
        $this->assertEquals(false, $result);
        $this->assertTrue($this->parser->parseBoolNull("null", $result));
        $this->assertEquals(null, $result);
        $this->assertTrue($this->parser->parseBoolNull(" null ", $result));
        $this->assertEquals(null, $result);
        $this->assertTrue($this->parser->parseBoolNull(" NuLl ", $result));
        $this->assertEquals(null, $result);

        $this->assertFalse($this->parser->parseBoolNull("test", $result));


    }

    public function testParseString()
    {
        $this->assertTrue($this->parser->parseString("'asd'", $result));
        $this->assertEquals("asd", $result);
        $this->assertTrue($this->parser->parseString(" 'asd' ", $result));
        $this->assertEquals("asd", $result);
        $this->assertTrue($this->parser->parseString('"asd"', $result));
        $this->assertEquals("asd", $result);
        $this->assertTrue($this->parser->parseString('"asd\""', $result));
        $this->assertEquals('asd"', $result);
        $this->assertTrue($this->parser->parseString('"asd\\\'"', $result));
        $this->assertEquals('asd\\\'', $result);
        $this->assertTrue($this->parser->parseString("'asd\\''", $result));
        $this->assertEquals('asd\'', $result);
        $this->assertTrue($this->parser->parseString("'asd\\\"'", $result));
        $this->assertEquals('asd\\"', $result);
        $this->assertTrue($this->parser->parseString("'\\n'", $result));
        $this->assertEquals('\\n', $result);
        $this->assertTrue($this->parser->parseString('"\n"', $result));
        $this->assertEquals("\n", $result);
        $this->assertTrue($this->parser->parseString('"\\\\n"', $result));
        $this->assertEquals("\\n", $result);
        $this->assertTrue($this->parser->parseString('"\v"', $result));
        $this->assertEquals("\v", $result);
        $this->assertTrue($this->parser->parseString('"\e"', $result));
        $this->assertEquals("\e", $result);
        $this->assertTrue($this->parser->parseString('"\f"', $result));
        $this->assertEquals("\f", $result);
        $this->assertTrue($this->parser->parseString('"\r"', $result));
        $this->assertEquals("\r", $result);
        $this->assertTrue($this->parser->parseString('"\t"', $result));
        $this->assertEquals("\t", $result);
        $this->assertTrue($this->parser->parseString('"\123"', $result));
        $this->assertEquals("\123", $result);
        $this->assertTrue($this->parser->parseString('"\x01"', $result));
        $this->assertEquals("\x01", $result);


        $this->assertFalse($this->parser->parseString("", $result));
    }


    public function testParseScalar()
    {
        $this->assertTrue($this->parser->parseScalar("'asd'", $result));
        $this->assertEquals("asd", $result);
        $this->assertTrue($this->parser->parseScalar(" true ", $result));
        $this->assertEquals(true, $result);
        $this->assertTrue($this->parser->parseScalar('0.1', $result));
        $this->assertEquals(0.1, $result);
        $this->assertTrue($this->parser->parseScalar('null', $result));
        $this->assertEquals(null, $result);
        $this->assertTrue($this->parser->parseScalar('1', $result));
        $this->assertEquals(1, $result);

        $this->assertFalse($this->parser->parseScalar("[1,2,3]", $result));
    }

    public function testParseArrayEntry()
    {
        $this->assertTrue($this->parser->parseArrayListEntry("123", $result));
        $this->assertEquals([123], $result);
        $this->assertTrue($this->parser->parseArrayListEntry("'a'=>123", $result));
        $this->assertEquals([123, 'a' => 123], $result);
        $result = null;
        $this->assertTrue($this->parser->parseArrayListEntry("'123a'", $result));
        $this->assertEquals(['123a'], $result);
        $result = null;
        $this->assertTrue($this->parser->parseArrayListEntry(" true ", $result));
        $this->assertEquals([true], $result);
        $result = null;
        $this->assertTrue($this->parser->parseArrayListEntry("Site.func()", $result));
        $this->assertEquals([new JSONFunctionImpl('func', new TypeImpl('Site'))], $result);

        $this->assertFalse($this->parser->parseArrayListEntry("a", $result));

    }

    public function testParseArrayList()
    {
        $this->assertTrue($this->parser->parseArrayList("1,2,3", $result));
        $this->assertEquals([1, 2, 3], $result);
        $result = null;
        $this->assertTrue($this->parser->parseArrayList("1,[2,5,'asd'],3", $result));
        $this->assertEquals([1, [2, 5, 'asd'], 3], $result);
        $result = null;
        $this->assertTrue($this->parser->parseArrayList("1,2=>3,4=>5,4=>6", $result));
        $this->assertEquals([1, 2 => 3, 4 => 6], $result);
        $result = null;
        $this->assertFalse($this->parser->parseArrayList("1,[2,3", $result));
        $this->assertFalse($this->parser->parseArrayList("1,,", $result));
        $this->assertFalse($this->parser->parseArrayList("1,", $result));
        $this->assertFalse($this->parser->parseArrayList("", $result));

    }

    public function testParseArray()
    {
        $this->assertTrue($this->parser->parseArray("[1,2,3]", $result));
        $this->assertEquals([1, 2, 3], $result);
        $this->assertTrue($this->parser->parseArray("[]", $result));
        $this->assertEquals([], $result);
        $this->assertTrue($this->parser->parseArray(" [] ", $result));
        $this->assertEquals([], $result);
        $this->assertTrue($this->parser->parseArray("[1,[2,[3]]]", $result));
        $this->assertEquals([1, [2, [3]]], $result);
        $this->assertFalse($this->parser->parseArray("[1,[2,3]", $result));
        $this->assertFalse($this->parser->parseArray("[11,]", $result));


    }

    public function testParseArgument()
    {
        $this->assertTrue($this->parser->parseArgument("'123a'", $result));
        $this->assertEquals('123a', $result);
        $result = null;
        $this->assertTrue($this->parser->parseArgument(" true ", $result));
        $this->assertEquals(true, $result);
        $result = null;
        $this->assertTrue($this->parser->parseArgument(" [] ", $result));
        $this->assertEquals([], $result);
        $result = null;
        $this->assertTrue($this->parser->parseArgument("Site.func()", $result));
        $this->assertEquals(new JSONFunctionImpl('func', new TypeImpl('Site')), $result);
        $this->assertTrue($this->parser->parseArgument("Site..func()..func2()", $result));
        $this->assertFalse($this->parser->parseArgument("Site", $result));
        $this->assertFalse($this->parser->parseArgument("Site.f\\u", $result));
        $this->assertFalse($this->parser->parseArgument("Site.\\u", $result));

    }

    public function testParseArgumentList()
    {
        $this->assertTrue($this->parser->parseArgumentList("2", $result));
        $this->assertEquals([2], $result);
        $this->assertTrue($this->parser->parseArgumentList("Site.f()", $result));
        $this->assertInstanceOf('ChristianBudde\cbweb\controller\json\JSONFunction', $result[0]);
        $this->assertTrue($this->parser->parseArgumentList("123,456,2", $result));
        $this->assertEquals([123, 456, 2], $result);
        $this->assertFalse($this->parser->parseArgumentList("", $result));
        $this->assertFalse($this->parser->parseArgumentList("asd", $result));
    }


    public function testParseFunction()
    {
        $f2 = new JSONFunctionImpl('f', new TypeImpl('SomeType'));
        $f = new JSONFunctionImpl('func', $this->nullTarget);

        $this->assertTrue($this->parser->parseFunction("func()", $result));
        $this->assertEquals($f, $result);
        $f->setArg(0, $f2);
        $this->assertTrue($this->parser->parseFunction("func(SomeType.f())", $result));
        $this->assertEquals($f, $result);
        $this->assertTrue($this->parser->parseFunction("func(SomeType..f()..f2())", $result));
        $f->setArg(0, 2);
        $this->assertTrue($this->parser->parseFunction(" func(2) ", $result));
        $this->assertEquals($f, $result);
        $f->setArg(1, 3);
        $f->setArg(2, 4);
        $this->assertTrue($this->parser->parseFunction("func(2,3,4)", $result));
        $this->assertEquals($f, $result);
        $this->assertFalse($this->parser->parseFunction("func(", $result));
        $this->assertFalse($this->parser->parseFunction("func(())", $result));
        $this->assertFalse($this->parser->parseFunction("func(asd)", $result));
        $this->assertFalse($this->parser->parseFunction("f-nc()", $result));
        $this->assertFalse($this->parser->parseFunction("f nc()", $result));
    }

    public function testParseFunctionCall()
    {
        $f = new JSONFunctionImpl('func', new TypeImpl('Site'));
        $f2 = new JSONFunctionImpl('f2', $f);
        $this->assertTrue($this->parser->parseFunctionCall("Site.func()", $result));
        $this->assertEquals($f, $result);
        $f->setArg(0, 2);
        $this->assertTrue($this->parser->parseFunctionCall(" Site.func(2) ", $result));
        $this->assertEquals($f, $result);
        $f->setArg(1, 3);
        $f->setArg(2, 4);

        $f3 = new JSONFunctionImpl("arrayAccess", new TypeImpl("POST"));
        $f3->setArg(0, $value = "SomeIndex");
        $this->assertTrue($this->parser->parseFunctionCall(" POST [\"$value\"] ", $result));
        $this->assertEquals($f3, $result);

        $this->assertTrue($this->parser->parseFunctionCall("Site . func(2,3,4)", $result));
        $this->assertEquals($f, $result);


        $this->assertTrue($this->parser->parseFunctionCall("Site . func(2,3,4).f2()", $result));
        $this->assertEquals($f2, $result);

    }


    public function testFunctionChain()
    {
        $f = new JSONFunctionImpl('func', $this->nullTarget);
        $f2 = new JSONFunctionImpl('func2', $this->nullTarget);
        $f3 = new JSONFunctionImpl('func3', $this->nullTarget);
        $this->assertTrue($this->parser->parseFunctionChain("func()", $result));
        $this->assertEquals($f, $result);

        $f2->setTarget($f);
        $this->assertTrue($this->parser->parseFunctionChain("func().func2()", $result));
        $this->assertEquals($f2, $result);
        $f2->setTarget($f);

        $f3->setTarget($f2);
        $this->assertTrue($this->parser->parseFunctionChain("func() . func2() . func3()", $result));
        $this->assertEquals($f3, $result);

    }

    public function testParseCompositeFunction()
    {
        $f = new JSONFunctionImpl('func', $this->nullTarget);
        $f2 = new JSONFunctionImpl('func2', $this->nullTarget);
        $f3 = new JSONFunctionImpl('func3', $this->nullTarget);

        $composite = new CompositeFunctionImpl($this->nullTarget);
        $composite->appendFunction($f);

        $this->assertTrue($this->parser->parseCompositeFunction("..func()", $result));
        $this->assertEquals($composite, $result);

        $composite->appendFunction($f2);
        $this->assertTrue($this->parser->parseCompositeFunction(" .. func() .. func2()", $result));
        $this->assertEquals($composite, $result);

        $composite->removeFunction($f2);
        $composite->appendFunction($f3);

        $f3->setTarget($f2);
        $this->assertTrue($this->parser->parseCompositeFunction(".. func() .. func2() . func3()", $result));
        $this->assertEquals($composite, $result);

    }

    public function testParseCompositeFunctionCall()
    {
        $f = new JSONFunctionImpl('func', new TypeImpl("Site"));
        $f2 = new JSONFunctionImpl('func2', $f);
        $f3 = new JSONFunctionImpl('func3', $this->nullTarget);

        $composite = new CompositeFunctionImpl($f2);
        $composite->appendFunction($f3);


        $this->assertTrue($this->parser->parseCompositeFunctionCall("Site.func().func2()..func3()", $result));
        $this->assertEquals($composite, $result);

    }

    public function testParseProgram()
    {
        $this->assertTrue($this->parser->parseProgram("Site.func()", $result));
        $this->assertTrue($this->parser->parseProgram("Site.func()..func2()..func3()..func4().func5()", $result));
    }

    public function testParseFunctionString()
    {
        $r = $this->parser->parseFunctionString("Site.func()..func2()..func3()..func4().func5()");
        $this->assertInstanceOf('ChristianBudde\cbweb\controller\json\Program', $r);
        $r = $this->parser->parseFunctionString("Logger.log(1,\"test\",[])");
        $this->assertInstanceOf('ChristianBudde\cbweb\controller\json\Program', $r);
        $r = $this->parser->parseFunctionString("Site.func().func5()");
        $this->assertInstanceOf('ChristianBudde\cbweb\controller\json\Program', $r);
        $r = $this->parser->parseFunctionString("Site.func()..func2()..func3()..func4.func5()");
        $this->assertNull($r);
    }


    /**
     * <program>                    = <composite_call> | <function_call>
     *
     * <composite_function_call>    = <target><function_chains>
     * <composite_function>         = [..<function_chain>]*
     * <function_chain>             = <function_chain>.<function> | <function>
     *
     * <function_call>              = <target>.<function>
     * <function>                   = <name>([<arg>, ...])
     * <target>                     = <function_call> | <name>
     * <arg>                        = <scalar> | <array> | <function_call>
     * <array>                      = \[ <array_index>, ... \]
     * <array_index>                = <scalar> => <arg> | <arg>
     * <scalar>                     = true | false | null | <num> | *string*
     * <num>                        = [+-]? <integer> | <float>
     * <integer>                    = *decimal* | *hexadecimal* | *octal* | *binary*
     * <float>                      = *double_number* | *exp_double_number*
     * @param string $input
     * @return JSONFunction
     */


}