<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 6:38 PM
 */

namespace ChristianBudde\Part\util\file;



use ChristianBudde\Part\controller\ajax\TypeHandlerGenerator;
use ChristianBudde\Part\controller\ajax\TypeHandlerLibrary;

class TypeHandlerGeneratorImageFileImpl extends ImageFileImpl implements TypeHandlerGenerator{


    private $typeHandlerLibrary;
    private $file;

    function __construct(TypeHandlerLibrary $typeHandlerLibrary, ImageFile $file)
    {
        parent::__construct($file->getAbsoluteFilePath());
        $this->typeHandlerLibrary = $typeHandlerLibrary;
        $this->file = $file;
    }



    public function generateTypeHandler()
    {
        return $this->typeHandlerLibrary->getImageFileTypeHandlerInstance($this->file);
    }

    public function copy($path)
    {
        return $this->wrapImage(parent::copy($path));
    }

    public function mirrorVertical($saveAsNewFile = false)
    {
        return $this->wrapImage(parent::mirrorVertical($saveAsNewFile));
    }

    public function mirrorHorizontal($saveAsNewFile = false)
    {
        return $this->wrapImage(parent::mirrorHorizontal($saveAsNewFile));
    }

    public function rotate($degree, $saveAsNewFile = false)
    {
        return $this->wrapImage(parent::rotate($degree, $saveAsNewFile));
    }

    public function crop($x, $y, $width, $height, $saveAsNewFile = false)
    {
        return $this->wrapImage(parent::crop($x, $y, $width, $height, $saveAsNewFile));
    }

    public function forceSize($width, $height, $saveAsNewFile = false)
    {
        return $this->wrapImage(parent::forceSize($width, $height, $saveAsNewFile));
    }


    private function wrapImage($file){
        return $file == null?null:new TypeHandlerGeneratorImageFileImpl($this->typeHandlerLibrary, $file);
    }

}