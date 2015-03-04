<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 9:24 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\util\file\File;

class FileTypeHandlerImpl extends GenericObjectTypeHandlerImpl{

    private $container;
    private $file;
    //TODO implement this
    function __construct(BackendSingletonContainer $container, File $file)
    {
        $this->container = $container;
        $this->file = $file;
    }


}