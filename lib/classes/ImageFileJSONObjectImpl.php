<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/7/14
 * Time: 6:03 PM
 */

class ImageFileJSONObjectImpl extends FileJSONObjectImpl{


    function __construct(ImageFile $file)
    {
        parent::__construct($file);
        $this->name = "image_file";
        $this->setVariable('width', $file->getWidth());
        $this->setVariable('height', $file->getHeight());
        $this->setVariable('ratio', $file->getRatio());
    }
}