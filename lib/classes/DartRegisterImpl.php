<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 18/01/13
 * Time: 11:39
 */
class DartRegisterImpl implements DartRegister
{
    private $registeredFiles = array();

    /**
     * Register a Dart file to be added to the site
     * @param DartFile $file
     * @return void
     */
    public function registerDartFile(DartFile $file)
    {
        $fileExists = false;
        /** @var $oldFile File */
        foreach($this->registeredFiles as $oldFile){
            $fileExists = $fileExists || $file->getAbsoluteFilePath() == $oldFile->getAbsoluteFilePath();
        }
        if(!$fileExists){
            $this->registeredFiles[] = $file;
        }
    }

    /**
     * Returns an array containing the registered Dart files
     * @return array
     */
    public function getRegisteredFiles()
    {
        return $this->registeredFiles;
    }


}
