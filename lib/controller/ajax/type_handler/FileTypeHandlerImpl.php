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
use ChristianBudde\Part\util\traits\TypeHandlerTrait;

class FileTypeHandlerImpl extends GenericObjectTypeHandlerImpl{

    private $container;

    use TypeHandlerTrait;

    function __construct(BackendSingletonContainer $container, File $file)
    {
        $this->container = $container;

        parent::__construct($file, 'File');
        $this->whitelistFunction("File",
            'getContents',
            'getFilename',
            'getExtension',
            'getBasename',
            'size',
            'getDataURI',
            'getModificationTime',
            'getCreationTime',
            'getPath'
        );

        $this->addTypeAuthFunction('File', $this->currentUserLoggedInAuthFunction($container));

        $this->addFunction('File', 'getPath', function (File $instance) {
            return $instance->getParentFolder()->getName()."/".$instance->getFilename();
        });
    }


}