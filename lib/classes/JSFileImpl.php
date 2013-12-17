<?php

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/6/12
 * Time: 4:50 PM
 * To change this template use File | Settings | File Templates.
 */
class JSFileImpl extends FileImpl implements JSFile
{
    /** @var $minimizer Optimizer | null */
    private $minimizer;

    /**
     * Will minimize the JS file, and save the original as [filename]-original
     * @return JSFile | bool Will return false if error, else The original JSFile
     */
    public function minimize()
    {
        if ($this->minimizer === null || !$this->exists()) {
            return false;
        }
        $resultPath = $this->filePath . '-result';
        $result = new JSFileImpl($resultPath);
        $minimizeResult = $this->minimizer->optimize($this, $result);
        if (!$minimizeResult) {
            return false;
        }
        $thisPath = $this->filePath;
        $originalPath = $thisPath . '-original';
        $this->move($originalPath);
        $result->move($thisPath);
        $this->filePath = $thisPath;
        $result->filePath = $originalPath;
        $this->write("\r/*minimized*/");

        return $result;

    }

    /**
     * @return bool
     */
    public function isMinimized()
    {
        return preg_match('/\/\*minimized\*\//', $this->getContents()) ? true : false;
    }

    public function copy($path)
    {
        $nFile = parent::copy($path);
        if ($nFile === null) {
            return null;
        }
        return new JSFileImpl($nFile->getAbsoluteFilePath());
    }

    /**
     * @param Optimizer $minimizer
     */
    public function setMinimizer(Optimizer $minimizer)
    {
        $this->minimizer = $minimizer;
    }

    /**
     * @return null | Optimizer
     */
    public function getMinimizer()
    {
        return $this->minimizer;
    }
}
