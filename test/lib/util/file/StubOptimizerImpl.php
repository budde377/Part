<?php
namespace ChristianBudde\Part\util\file;




/**
 * User: budde
 * Date: 6/3/12
 * Time: 11:48 PM
 */
class StubOptimizerImpl implements Optimizer
{
    /** @var bool */
    private $optimizeReturn;

    /**
     * @param bool $optimizeReturn
     */
    public function __construct($optimizeReturn)
    {
        $this->optimizeReturn = $optimizeReturn;
    }

    /**
     * @param File $file
     * @param File $outputFile
     * @return bool
     */
    public function optimize(File $file, File $outputFile)
    {
        return $this->optimizeReturn;

    }

    /**
     * @return boolean
     */
    public function getOptimizeReturn()
    {
        return $this->optimizeReturn;
    }

    /**
     * @param boolean $optimizeReturn
     */
    public function setOptimizeReturn($optimizeReturn)
    {
        $this->optimizeReturn = $optimizeReturn;
    }
}
