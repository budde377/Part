<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 6:43 PM
 */

namespace ChristianBudde\Part\test;


use ChristianBudde\Part\controller\ajax\FileTypeHandlerGeneratorImpl;
use ChristianBudde\Part\test\stub\StubTypeHandlerLibraryImpl;
use ChristianBudde\Part\util\file\ImageFileImpl;

class ImageFileTypeHandlerGeneratorImplTest extends \PHPUnit_Framework_TestCase{


    public function testGenerator()
    {

        $generator = new FileTypeHandlerGeneratorImpl(new StubTypeHandlerLibraryImpl(), $file = new ImageFileImpl('nonExistingFile'));
        $this->assertTrue($generator->generateTypeHandler() === $file);
    }
}