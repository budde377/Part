<?php

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/1/12
 * Time: 10:56 AM
 * To change this template use File | Settings | File Templates.
 */
interface JSFile extends File
{
    /**
     * @abstract
     * Will minimize the JS file, and save the original as [filename]-original
     * @return JSFile | bool Will return false if error, else The original JSFile
     */
    public function minimize();

    /**
     * @abstract
     * @return bool
     */
    public function isMinimized();

    /**
     * @param Optimizer $minimizer
     */
    public function setMinimizer(Optimizer $minimizer);

    /**
     * @return null | Optimizer
     */
    public function getMinimizer();

}
