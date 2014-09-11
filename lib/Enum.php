<?php
namespace ChristianBudde\cbweb;
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/6/12
 * Time: 5:34 PM
 * To change this template use File | Settings | File Templates.
 */
interface Enum
{
    /**
     * @static
     * @abstract
     * @param string $value
     * @return bool
     */
    public static function hasConstantWithValue($value);
}
