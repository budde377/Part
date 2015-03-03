<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 9:24 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\util\file\ImageFile;

class ImageFileTypeHandlerImpl extends GenericObjectTypeHandlerImpl{

    private $container;
    private $file;

    function __construct(BackendSingletonContainer $container, ImageFile $file)
    {
        $this->container = $container;
        $this->file = $file;
    }


}