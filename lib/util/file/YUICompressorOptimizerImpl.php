<?php
namespace ChristianBudde\Part\util\file;

use ChristianBudde\Part\exception\MalformedParameterException;


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
        while ($tempFile->exists()) {
            $tempName = uniqid($outputFile->getAbsoluteFilePath());
            $tempFile = $outputFile->copy($tempName);
        }

        $command = "yui-compressor --type {$this->type} {$file->getAbsoluteFilePath()} > {$tempFile->getAbsoluteFilePath()}";
        exec($command, $v = null, $retVal);
        if ($retVal != 0 || !$tempFile->exists()) {
            $logger = new LogFileImpl(dirname(__FILE__) . '/../../../../log');
            $tempFile->delete();
            $logger->log( "Compression failed with command: '$command'",1);
            return false;
        }
        $outputFile->delete();
        $tempFile->move($outputFile->getAbsoluteFilePath());


        return true;
    }
}
