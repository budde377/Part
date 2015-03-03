<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 6:38 PM
 */

namespace ChristianBudde\Part\controller\ajax;


use ChristianBudde\Part\util\file\ImageFile;

class ImageFileTypeHandlerGeneratorImpl extends FileTypeHandlerGeneratorImpl{


    private $typeHandlerLibrary;
    private $file;

    function __construct(TypeHandlerLibrary $typeHandlerLibrary, ImageFile $file)
    {
        $this->typeHandlerLibrary = $typeHandlerLibrary;
        $this->file = $file;
    }



    public function generateTypeHandler()
    {
        return $this->typeHandlerLibrary->getImageFileTypeHandlerInstance($this->file);
    }


}