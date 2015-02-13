<?php
namespace ChristianBudde\Part\util\file;



/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/3/12
 * Time: 8:44 PM
 * To change this template use File | Settings | File Templates.
 */
class CSSFileImpl extends FileImpl implements CSSFile
{
    /** @var $minimizer Optimizer | null */
    private $minimizer = null;

    /**
     * @param string $file
     */
    public function __construct($file)
    {
        parent::__construct($file);
    }

    /**
     * Will minimize the CSS file, and save the original as [filename]-original
     * @return bool | CSSFile
     */
    public function minimize()
    {
        if ($this->minimizer === null) {
            return false;
        }
        $resultPath = $this->filePath . "-result";
        $resultFile = new CSSFileImpl($resultPath);
        if (!$this->exists()) {
            return false;
        }

        $minRet = $this->minimizer->optimize($this, $resultFile);

        if (!$minRet) {
            return $minRet;
        }

        $thisPath = $this->filePath;
        $originalPath = $thisPath . '-original';
        $this->move($originalPath);
        $resultFile->move($thisPath);
        $this->filePath = $thisPath;
        $resultFile->filePath = $originalPath;

        $this->write("\r/*minimized*/");

        return $resultFile;
    }

    /**
     * @param $path
     * @return CSSFile|File|null
     */
    public function copy($path)
    {
        $newFile = parent::copy($path);
        if ($newFile instanceof File) {
            /** @var $newFile File */
            return new CSSFileImpl($newFile->getAbsoluteFilePath(), $this->minimizer);
        }
        return $newFile;
    }

    /**
     * @return bool
     */
    public function isMinimized()
    {
        return preg_match('/\/\*minimized\*\//', $this->getContents()) ? true : false;
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
