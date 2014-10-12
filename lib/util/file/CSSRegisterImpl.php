<?php
namespace ChristianBudde\cbweb\util\file;


/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/13/12
 * Time: 5:44 PM
 * To change this template use File | Settings | File Templates.
 */
class CSSRegisterImpl implements CSSRegister
{

    private $registeredFiles = array();

    /**
     * Register a CSS file to be added to the site
     * @param CSSFile $file
     * @return void
     */
    public function registerCSSFile(CSSFile $file)
    {
        $match = false;
        foreach ($this->registeredFiles as $f) {
            $match = $this->compareFiles($f, $file);
        }
        if (!$match) {
            $this->registeredFiles[] = $file;
        }
    }

    /**
     * Get an array with the registered files
     * @return array
     */
    public function getRegisteredFiles()
    {
        return $this->registeredFiles;
    }


    private function compareFiles(File $file1, File $file2)
    {
        return $file1->getAbsoluteFilePath() == $file2->getAbsoluteFilePath();
    }

}
