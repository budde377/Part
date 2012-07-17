<?php
require_once dirname(__FILE__) . '/../_helper/ClassHelper.php';
require_once dirname(__FILE__) . '/_stub/StubEnum.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/6/12
 * Time: 5:59 PM
 * To change this template use File | Settings | File Templates.
 */
class ClassHelperTest extends PHPUnit_Framework_TestCase
{

    public function testClassHasConstantWithValueWillReturnTrueIfClassHasConstantWithValue()
    {
        $res = ClassHelper::classHasConstantWithValue('StubEnum', 1);
        $this->assertTrue($res, 'Did not have constant with req. value');
    }

    public function testClassHasConstantWithValueWillReturnFalseIfClassHasConstantWithValue()
    {
        $res = ClassHelper::classHasConstantWithValue('StubEnum', 2);
        $this->assertFalse($res, 'Did not have constant with req. value');
    }


}
