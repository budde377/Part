<?php
namespace ChristianBudde\cbweb\test\stub;

use ChristianBudde\cbweb\util\file\File;
use ChristianBudde\cbweb\util\file\Optimizer;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/11/12
 * Time: 9:47 AM
 * To change this template use File | Settings | File Templates.
 */
class NullOptimizerImpl implements Optimizer
{

    /**
     * @param File $file
     * @param \ChristianBudde\cbweb\util\file\File $outputFile
     * @return bool
     */
    public function optimize(File $file, File $outputFile)
    {
        return null;
    }
}