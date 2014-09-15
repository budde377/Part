<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/7/14
 * Time: 6:03 PM
 */
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\controller\json\ImageFileJSONObjectImpl;
use ChristianBudde\cbweb\util\file\ImageFileImpl;
use PHPUnit_Framework_TestCase;

class ImageFileJSONObjectImplTest extends PHPUnit_Framework_TestCase
{

    public function testConstructorSetsVariablesWhenImageFile()
    {
        $file = new ImageFileImpl(dirname(__FILE__) . '/stubs/imageFileStub300x200.png');
        $object = new ImageFileJSONObjectImpl($file);
        $this->assertEquals('image_file', $object->getName());
        $this->assertEquals($file->getFilename(), $object->getVariable('filename'));
        $this->assertEquals($file->getBasename(), $object->getVariable('basename'));
        $this->assertEquals($file->getExtension(), $object->getVariable('extension'));
        $this->assertEquals($file->size(), $object->getVariable('size'));
        $this->assertEquals($file->getWidth(), $object->getVariable('width'));
        $this->assertEquals($file->getHeight(), $object->getVariable('height'));
        $this->assertEquals($file->getRatio(), $object->getVariable('ratio'));
        $this->assertEquals($file->getMimeType(), $object->getVariable('mime_type'));
        $this->assertEquals($file->getModificationTime(), $object->getVariable('modified'));
        $this->assertEquals($file->getCreationTime(), $object->getVariable('created'));

    }

}