<?php
namespace ChristianBudde\Part\util\file;




/**
 * User: budde
 * Date: 6/11/12
 * Time: 9:47 AM
 */
class NullOptimizerImpl implements Optimizer
{

    /**
     * @param File $file
     * @param \ChristianBudde\Part\util\file\File $outputFile
     * @return bool
     */
    public function optimize(File $file, File $outputFile)
    {
        return null;
    }
}
