<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 2/1/15
 * Time: 9:17 AM
 */

namespace ChristianBudde\cbweb\controller\function_string\ast;


class EmptyArrayImpl implements EmptyArray{

    public function toJSON()
    {
        return [];
    }

    /**
     * @return array
     */
    public function toArgumentArray()
    {
        return [[]];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->toArgumentArray();
    }
}