<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 6:43 PM
 */

namespace ChristianBudde\Part\test;


use ChristianBudde\Part\test\stub\StubTypeHandlerLibraryImpl;
use ChristianBudde\Part\util\file\File;
use ChristianBudde\Part\util\file\ImageFileImpl;
use ChristianBudde\Part\util\file\TypeHandlerGeneratorImageFileImpl;

class TypeHandlerGeneratorImageFileImplTest extends \PHPUnit_Framework_TestCase{
    /** @var  TypeHandlerGeneratorImageFileImpl */
    private $generator;
    /** @var  File */
    private $file;

    protected function setUp()
    {
        $this->generator = new TypeHandlerGeneratorImageFileImpl(new StubTypeHandlerLibraryImpl(), $this->file = new ImageFileImpl('nonExistingFile'));
    }


    public function testGenerator()
    {

        $this->assertTrue($this->generator->generateTypeHandler() === $this->file);
    }

    public function testPath()
    {

        $this->assertEquals($this->generator->getAbsoluteFilePath(),$this->file->getAbsoluteFilePath() );
    }


}