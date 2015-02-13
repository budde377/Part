<?php
namespace ChristianBudde\Part\exception;

use Exception;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 5/14/12
 * Time: 5:07 PM
 * To change this template use File | Settings | File Templates.
 */
class FileNotFoundException extends Exception
{

    private $fileName;
    private $fileDesc;

    /**
     * @param string $file
     * @param string $fileDesc
     */
    public function __construct($file, $fileDesc = '')
    {

        $this->fileName = $file;
        $this->fileDesc = $fileDesc;
        if (strlen($fileDesc)) {
            $fileDesc = "($fileDesc)";
        }
        parent::__construct('FileNotFoundException: The file "' . $file . '" ' . $fileDesc . ' was not found!');
    }

    /**
     * @return string
     */
    public function getFileDesc()
    {
        return $this->fileDesc;
    }


    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }


}
