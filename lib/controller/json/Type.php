<?php
namespace ChristianBudde\Part\controller\json;

/**
 * Created by PhpStorm.
 * User: budde
 * Date: 8/28/14
 * Time: 12:58 PM
 */

interface Type extends Target {

    /**
     * @return string
     */
    public function getTypeString();
} 