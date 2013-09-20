<?php
require_once dirname(__FILE__) . '/../_interface/Optimizer.php';
require_once dirname(__FILE__) . '/YUICompressorOptimizerImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/6/12
 * Time: 5:24 PM
 * To change this template use File | Settings | File Templates.
 */
class JSYUICompressorOptimizerImpl implements Optimizer
{

    /**
     * @param File $file
     * @param File $outputFile
     * @return bool
     */
    public function optimize(File $file, File $outputFile)
    {
        $YUIOptimizer = new YUICompressorOptimizerImpl(YUICompressorOptimizerImpl::COMPRESSION_TYPE_JS);
        return $YUIOptimizer->optimize($file, $outputFile);
    }
}
