<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 6:37 PM
 */

namespace ChristianBudde\Part\controller\ajax;


use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
use ChristianBudde\Part\util\file\File;

class FileTypeHandlerGeneratorImpl implements TypeHandlerGenerator{

    private $typeHandlerLibrary;
    private $file;

    function __construct(TypeHandlerLibrary $typeHandlerLibrary, File $file)
    {
        $this->typeHandlerLibrary = $typeHandlerLibrary;
        $this->file = $file;
    }


    /**
     * @return TypeHandler
     */
    public function generateTypeHandler()
    {
        return $this->typeHandlerLibrary->getFileTypeHandlerInstance($this->file);
    }
}