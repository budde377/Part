<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/3/12
 * Time: 11:26 PM
 * To change this template use File | Settings | File Templates.
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
