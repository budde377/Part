<?php
require_once dirname(__FILE__) . '/../_class/CSSFileImpl.php';
require_once dirname(__FILE__) . '/_stub/StubOptimizerImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/3/12
 * Time: 8:46 PM
 * To change this template use File | Settings | File Templates.
 */
class CSSFileImplTest extends PHPUnit_Framework_TestCase
{

    // This test file assumes that CSSFileImpl extends FileImpl.

    public function testCopyReturnInstanceOfCSSFile()
    {
        $file = dirname(__FILE__) . '/_stub/fileStub';
        $cssFile = new CSSFileImpl($file);
        $this->assertTrue($cssFile->fileExists(), 'File did not exists to begin with');
        $cssCopy = $cssFile->copy($file . '2');
        $this->assertInstanceOf('CSSFile', $cssCopy);
        $cssCopy->delete();
    }

    public function testCopyReturnNullOnFileNotFound()
    {
        $file = dirname(__FILE__) . '/_stub/noSuchFile';
        $cssFile = new CSSFileImpl($file);
        $this->assertFalse($cssFile->fileExists(), 'File did exists to begin with');
        $cssCopy = $cssFile->copy($file . '2');
        $this->assertNull($cssCopy, 'Did not return null');
    }

    public function testMinimizeWillReturnFalseWithNoMinimizerSet()
    {
        $file = dirname(__FILE__) . '/_stub/cssStub.css';
        $cssFile = new CSSFileImpl($file);
        $this->assertNull($cssFile->getMinimizer(), 'Minimizer was set');
        $ret = $cssFile->minimize();
        $this->assertFalse($ret, 'Did not return false');
    }

    public function testMinimizeWillReturnFalseOnFileNotFound()
    {
        $file = dirname(__FILE__) . '/_stub/notARealFile';
        $cssFile = new CSSFileImpl($file);
        $optimizer = new StubOptimizerImpl(true);
        $cssFile->setMinimizer($optimizer);
        $ret = $cssFile->minimize();
        $this->assertFalse($ret, 'Did not return false');
    }

    public function testMinimizeWillReturnFalseIfMinimizeDoes()
    {
        $file = dirname(__FILE__) . '/_stub/cssStub.css';
        $cssFile = new CSSFileImpl($file);
        $optimizer = new StubOptimizerImpl(false);
        $cssFile->setMinimizer($optimizer);
        $ret = $cssFile->minimize();
        $this->assertFalse($ret, 'Did not return false');

    }

    public function testMinimizeWillReturnOriginalCSSFileOnSuccess()
    {
        $file = dirname(__FILE__) . '/_stub/cssStub.css';
        $fileCopy = dirname(__FILE__) . '/_stub/cssStub2.css';
        $cssFile = new CSSFileImpl($file);
        $cssFile = $cssFile->copy($fileCopy);
        $optimizer = new StubOptimizerImpl(true);
        $cssFile->setMinimizer($optimizer);
        $originalContent = $cssFile->getContents();
        /** @var $ret CSSFile */
        $ret = $cssFile->minimize();
        $this->assertInstanceOf('CSSFile', $ret, 'Did not return CSSFile');
        $this->assertEquals($fileCopy . '-original', $ret->getAbsoluteFilePath(), 'Did not return CSSFile with right path');
        $this->assertTrue($cssFile->isMinimized(), 'File was not minimized');
        $this->assertEquals($originalContent, $ret->getContents(), 'Content did not match');
        $this->assertTrue($ret->fileExists(), 'Original file did not exist');
        $cssFile->delete();
        $ret->delete();
    }

}
