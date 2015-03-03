<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 6:39 PM
 */

namespace ChristianBudde\Part\test;


use ChristianBudde\Part\controller\ajax\FileTypeHandlerGeneratorImpl;
use ChristianBudde\Part\test\stub\StubTypeHandlerLibraryImpl;
use ChristianBudde\Part\util\file\FileImpl;

class FileTypeHandlerGeneratorImplTest extends \PHPUnit_Framework_TestCase{


    public function testGenerator()
    {

        $generator = new FileTypeHandlerGeneratorImpl(new StubTypeHandlerLibraryImpl(), $file = new FileImpl('nonExistingFile'));
        $this->assertTrue($generator->generateTypeHandler() === $file);
    }
}