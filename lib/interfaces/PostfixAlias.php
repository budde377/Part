<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/3/14
 * Time: 2:13 PM
 */

interface PostfixAlias extends PostfixAddress{

    /**
     * @return array of strings indicating targets.
     */
    public function listTargets();

    /**
     * Adds a target to the alias.
     * Duplicates will not be added.
     * @param $target
     * @return void
     */
    public function addTarget($target);

    /**
     * Remove a target if exists.
     * @param $target
     * @return mixed
     */
    public function removeTarget($target);


    /**
     * Tests if has target.
     * @param $target
     * @return bool
     */
    public function hasTarget($target);
} 