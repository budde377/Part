<?php
namespace ChristianBudde\cbweb;
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/7/14
 * Time: 5:31 PM
 */

class FileJSONObjectImpl extends JSONObjectImpl{


    function __construct(File $file)
    {
        parent::__construct("file");
        $this->setVariable('filename', $file->getFilename());
        $this->setVariable('basename', $file->getBasename());
        $this->setVariable('extension', $file->getExtension());
        $this->setVariable('size', $file->size());
        $this->setVariable('mime_type', $file->getMimeType());
        $this->setVariable('modified', $file->getModificationTime());
        $this->setVariable('created', $file->getCreationTime());

    }
}