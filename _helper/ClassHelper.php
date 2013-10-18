<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/6/12
 * Time: 5:46 PM
 * To change this template use File | Settings | File Templates.
 */
class ClassHelper
{
    public static function classHasConstantWithValue($class, $value)
    {
        $reflectionClass = new ReflectionClass($class);
        $constants = $reflectionClass->getConstants();
        if (array_search($value, $constants) !== false) {
            return true;
        }

        return false;

    }
}
