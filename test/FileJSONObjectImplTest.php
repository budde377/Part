<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 9/7/14
 * Time: 5:35 PM
 */

use ChristianBudde\cbweb\FileImpl;
use ChristianBudde\cbweb\FileJSONObjectImpl;

class FileJSONObjectImplTest extends PHPUnit_Framework_TestCase{

    public function testConstructorChangesName(){
        $file = new FileImpl(dirname(__FILE__).'/stubs/fileStub');
        $object = new FileJSONObjectImpl($file);
        $this->assertEquals('file', $object->getName());
        $this->assertEquals($file->getFilename(), $object->getVariable('filename'));
        $this->assertEquals($file->getBasename(), $object->getVariable('basename'));
        $this->assertEquals($file->getExtension(), $object->getVariable('extension'));
        $this->assertEquals($file->size(), $object->getVariable('size'));
        $this->assertEquals($file->getMimeType(), $object->getVariable('mime_type'));
        $this->assertEquals($file->getModificationTime(), $object->getVariable('modified'));
        $this->assertEquals($file->getCreationTime(), $object->getVariable('created'));

    }

} 