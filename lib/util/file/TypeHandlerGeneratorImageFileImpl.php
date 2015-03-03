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


}