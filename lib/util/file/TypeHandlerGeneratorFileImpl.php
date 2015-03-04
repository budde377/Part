<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 6:37 PM
 */

namespace ChristianBudde\Part\util\file;


use ChristianBudde\Part\controller\ajax\type_handler\TypeHandler;
use ChristianBudde\Part\controller\ajax\TypeHandlerGenerator;
use ChristianBudde\Part\controller\ajax\TypeHandlerLibrary;

class TypeHandlerGeneratorFileImpl extends FileImpl implements TypeHandlerGenerator{

    private $typeHandlerLibrary;
    private $file;

    function __construct(TypeHandlerLibrary $typeHandlerLibrary, File $file)
    {
        parent::__construct($file->getAbsoluteFilePath());
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

    public function copy($path)
    {
        $file = parent::copy($path);
        return $file == null?null:new TypeHandlerGeneratorFileImpl($this->typeHandlerLibrary, $file);
    }


}