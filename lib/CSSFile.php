<?php
namespace ChristianBudde\cbweb;
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/1/12
 * Time: 10:56 AM
 * To change this template use File | Settings | File Templates.
 */
interface CSSFile extends File
{
    /**
     * @abstract
     * Will minimize the CSS file, and save the original as [filename]-original
     * @return CSSFile | bool Will return false if error, else The original CSSFile
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
