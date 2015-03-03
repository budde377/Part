<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 6:39 PM
 */

namespace ChristianBudde\Part\test;


use ChristianBudde\Part\test\stub\StubTypeHandlerLibraryImpl;
use ChristianBudde\Part\util\file\FileImpl;
use ChristianBudde\Part\util\file\TypeHandlerGeneratorFileImpl;

class TypeHandlerGeneratorFileImplTest extends \PHPUnit_Framework_TestCase{

    /** @var  TypeHandlerGeneratorFileImpl */
    private $generator;
    /** @var  FileImpl */
    private $file;

    protected function setUp()
    {
        $this->generator = new TypeHandlerGeneratorFileImpl(new StubTypeHandlerLibraryImpl(), $this->file = new FileImpl('nonExistingFile'));
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