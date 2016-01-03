<?php
namespace ChristianBudde\Part\util\file;


/**
 * User: budde
 * Date: 6/3/12
 * Time: 11:26 PM
 */
interface Optimizer
{
    /**
     * @abstract
     * @param File $file
     * @param File $outputFile
     * @return bool
     */
    public function optimize(File $file, File $outputFile);
}
