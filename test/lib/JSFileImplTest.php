<?php
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\util\file\JSFileImpl;

use ChristianBudde\cbweb\util\traits\FilePathTrait;
use PHPUnit_Framework_TestCase;
use ChristianBudde\cbweb\test\stub\StubOptimizerImpl;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/3/12
 * Time: 8:46 PM
 * To change this template use File | Settings | File Templates.
 */
class JSFileImplTest extends PHPUnit_Framework_TestCase
{

    use FilePathTrait;
    // This test file assumes that JSFileImpl extends FileImpl.

    public function testCopyReturnInstanceOfJSFile()
    {
        $file = dirname(__FILE__) . '/../stubs/fileStub';
        $jsFile = new JSFileImpl($file);
        $this->assertTrue($jsFile->exists(), 'File did not exists to begin with');
        $jsCopy = $jsFile->copy($file . '2');
        $this->assertInstanceOf('ChristianBudde\cbweb\util\file\JSFile', $jsCopy);
        $jsCopy->delete();
    }

    public function testCopyReturnNullOnFileNotFound()
    {
        $file = dirname(__FILE__) . '/stubs/noSuchFile';
        $jsFile = new JSFileImpl($file);
        $this->assertFalse($jsFile->exists(), 'File did exists to begin with');
        $jsCopy = $jsFile->copy($file . '2');
        $this->assertNull($jsCopy, 'Did not return null');
    }

    public function testSetMinimizerWillSetMinimizer()
    {
        $file = dirname(__FILE__) . '/stubs/jsStub.js';
        $jsFile = new JSFileImpl($file);
        $this->assertNull($jsFile->getMinimizer(), 'Minimizer was set');
        $optimizer = new StubOptimizerImpl(true);
        $jsFile->setMinimizer($optimizer);
        $this->assertEquals($optimizer, $jsFile->getMinimizer(), 'Minimizer was not set');
    }

    public function testMinimizeWillReturnFalseWithNoMinimizerSet()
    {
        $file = dirname(__FILE__) . '/stubs/jsStub.js';
        $jsFile = new JSFileImpl($file);
        $this->assertNull($jsFile->getMinimizer(), 'Minimizer was set');
        $ret = $jsFile->minimize();
        $this->assertFalse($ret, 'Did not return false');
    }

    public function testMinimizeWillReturnFalseOnFileNotFound()
    {
        $file = dirname(__FILE__) . '/stubs/notARealFile';
        $jsFile = new JSFileImpl($file);
        $optimizer = new StubOptimizerImpl(true);
        $jsFile->setMinimizer($optimizer);
        $ret = $jsFile->minimize();
        $this->assertFalse($ret, 'Did not return false');
    }

    public function testMinimizeWillReturnFalseIfMinimizeDoes()
    {
        $file = dirname(__FILE__) . '/stubs/jsStub.js';
        $jsFile = new JSFileImpl($file);
        $optimizer = new StubOptimizerImpl(false);
        $jsFile->setMinimizer($optimizer);
        $ret = $jsFile->minimize();
        $this->assertFalse($ret, 'Did not return false');

    }

    public function testMinimizeWillReturnOriginalJSFileOnSuccess()
    {
        $file = dirname(__FILE__) . '/../stubs/jsStub.js';
        $fileCopy = dirname(__FILE__) . '/../stubs/jsStub2.js';
        $jsFile = new JSFileImpl($file);
        $jsFile = $jsFile->copy($fileCopy);
        $optimizer = new StubOptimizerImpl(true);
        $jsFile->setMinimizer($optimizer);
        $originalContent = $jsFile->getContents();
        /** @var $ret \ChristianBudde\cbweb\util\file\CSSFile */
        $ret = $jsFile->minimize();
        $this->assertInstanceOf('ChristianBudde\cbweb\util\file\JSFile', $ret, 'Did not return JSFile');
        $this->assertEquals($this->relativeToAbsolute($fileCopy . '-original'), $ret->getAbsoluteFilePath(), 'Did not return JSFile with right path');
        $this->assertTrue($jsFile->isMinimized(), 'File was not minimized');
        $this->assertEquals($originalContent, $ret->getContents(), 'Content did not match');
        $this->assertTrue($ret->exists(), 'Original file did not exist');
        $jsFile->delete();
        $ret->delete();
    }


}
