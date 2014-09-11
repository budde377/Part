<?php
use ChristianBudde\cbweb\File;
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/11/12
 * Time: 9:47 AM
 * To change this template use File | Settings | File Templates.
 */
class NullOptimizerImpl implements \ChristianBudde\cbweb\Optimizer
{

    /**
     * @param File $file
     * @param File $outputFile
     * @return bool
     */
    public function optimize(File $file, File $outputFile)
    {
        return null;
    }
}
