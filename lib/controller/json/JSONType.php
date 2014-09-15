<?php
namespace ChristianBudde\cbweb\controller\json;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 12:58 PM
 */

interface JSONType extends JSONTarget {

    /**
     * @return string
     */
    public function getTypeString();
} 