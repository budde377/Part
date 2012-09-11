<?php
require_once dirname(__FILE__) . '/../_interface/Optimizer.php';
require_once dirname(__FILE__) . '/../_exception/MalformedParameterException.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/6/12
 * Time: 5:24 PM
 * To change this template use File | Settings | File Templates.
 */
class YUICompressorOptimizerImpl implements Optimizer
{
    /** @var $type string */
    private $type;

    const COMPRESSION_TYPE_CSS = 'css';
    const COMPRESSION_TYPE_JS = 'js';

    public function __construct($type)
    {

        if ($type == YUICompressorOptimizerImpl::COMPRESSION_TYPE_CSS || $type == YUICompressorOptimizerImpl::COMPRESSION_TYPE_JS) {
            $this->type = $type;
        } else {
            throw new MalformedParameterException('YUICompressorOptimizerImpl[const]', 1);
        }

    }

    /**
     * @param File $file
     * @param File $outputFile
     * @return bool
     */
    public function optimize(File $file, File $outputFile)
    {
        $tempName = uniqid($outputFile->getAbsoluteFilePath());
        $tempFile = $outputFile->copy($tempName);
        while ($tempFile->fileExists()) {
            $tempName = uniqid($outputFile->getAbsoluteFilePath());
            $tempFile = $outputFile->copy($tempName);
        }

        $command = "yui-compressor --type {$this->type} {$file->getAbsoluteFilePath()} > {$tempFile->getAbsoluteFilePath()}";
        exec($command, $v = null, $retVal);
        if ($retVal != 0 || !$tempFile->fileExists()) {
            $logger = new LoggerImpl(dirname(__FILE__) . '/../');
            $tempFile->delete();
            $logger->log($this, "Compression failed with command: '$command'");
            return false;
        }
        $outputFile->delete();
        $tempFile->move($outputFile->getAbsoluteFilePath());


        return true;
    }
}
