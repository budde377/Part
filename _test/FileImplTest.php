<?php
require_once dirname(__FILE__) . '/../_class/FileImpl.php';

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/1/12
 * Time: 9:38 PM
 */
class FileImplTest extends PHPUnit_Framework_TestCase
{

    public function testFileExistsReturnFalseOnFileNotFound()
    {
        $file = new FileImpl('NotAnExistingFile');
        $this->assertFalse($file->fileExists(), 'Did not return false on non-existing file');
    }

    public function testFileExistsReturnTrueOnFileExists()
    {
        $filePath = dirname(__FILE__) . '/_stub/templateStub';
        $file = new FileImpl($filePath);
        $this->assertTrue($file->fileExists(), 'Did not return true on existing file');
    }

    public function testFileExistReturnFalseOnDirectory()
    {
        $filePath = dirname(__FILE__) .'/_stub';
        $file = new FileImpl($filePath);
        $this->assertFalse($file->fileExists(),'Did not return false on directory');
    }

    public function testGetContentsReturnEmptyStringOnFileNotFound()
    {
        $file = new FileImpl('NotAnExistingFile');
        $this->assertTrue('' === $file->getContents(), 'Did not return empty string on file not found');
    }

    public function testGetContentsReturnContentOnFileFound()
    {
        $filePath = dirname(__FILE__) . '/_stub/templateStub';
        $file = new FileImpl($filePath);
        $this->assertEquals(file_get_contents($filePath), $file->getContents(), 'Content did not match');
    }


    public function testGetAbsolutePathReturnAbsolutePathToFile()
    {
        $filePath = dirname(__FILE__) . '/.././_test/_stub/templateStub';
        $altFilePath = '_stub/templateStub';
        $file = new FileImpl($filePath);
        $this->assertEquals(dirname(__FILE__) . '/' . $altFilePath, $file->getAbsoluteFilePath(),
            'Did not return right absolute file path');
    }

    public function testGetRelativePathReturnRelativePathToFile()
    {
        $file = dirname(__FILE__) . '/someFile';
        $relativeDir = dirname(__FILE__) . '/../_class';
        $expected = '../_test/someFile';
        $file = new FileImpl($file);
        $this->assertEquals($expected, $file->getRelativeFilePathTo($relativeDir), 'Paths did not match');
    }

    public function testGetFileNameReturnsFileName()
    {
        $file = 'someFile';
        $path = dirname(__FILE__) . '/' . $file;
        $f = new FileImpl($path);
        $this->assertEquals($file, $f->getFileName(), 'Did not return expected filename');
    }

    public function testGetFileNameReturnsFileNameIfFileIsDir()
    {
        $file = 'someFile';
        $path = dirname(__FILE__) . '/' . $file . '/';
        $f = new FileImpl($path);
        $this->assertEquals($file, $f->getFileName(), 'Did not return expected filename');
    }

    public function testMoveMovesAndReturnTrueOnSuccess()
    {
        $filePath = dirname(__FILE__) . '/_stub/fileStub';
        $file = new FileImpl($filePath);
        $this->assertTrue($file->fileExists(), 'File did not exist to begin with.');
        $this->assertTrue($file->move($filePath . '2'), 'Move did not return true');
        $this->assertEquals($filePath . '2', $file->getAbsoluteFilePath(), 'The path was not updated.');
        $this->assertTrue($file->fileExists(), 'The file was not moved');
        $file->move($filePath);
    }

    public function testMoveReturnFalseOnFileNotFound()
    {
        $filePath = dirname(__FILE__) . '/_stub/notAReadFile';
        $file = new FileImpl($filePath);
        $this->assertFalse($file->fileExists(), 'File did exist to begin with.');
        $this->assertFalse($file->move($filePath . '2'), 'Move did return true');
        $this->assertEquals($filePath, $file->getAbsoluteFilePath(), 'The path was updated.');

    }

    public function testCopyCopiesAndReturnFileOnSuccess()
    {
        $filePath = dirname(__FILE__) . '/_stub/fileStub';
        $file = new FileImpl($filePath);
        $this->assertTrue($file->fileExists(), 'File did not exist to begin with.');
        $newFile = $file->copy($filePath . '2');
        $this->assertInstanceOf('File', $newFile, 'Did not return an instance of File');
        $this->assertEquals($filePath . '2', $newFile->getAbsoluteFilePath(), 'New file did not have right path');
        $this->assertTrue($newFile->fileExists(), 'The new file did note exists');
        unlink($newFile->getAbsoluteFilePath());
    }

    public function testCopyReturnNullOnNoFile()
    {
        $filePath = dirname(__FILE__) . '/_stub/notAReadFile';
        $file = new FileImpl($filePath);
        $this->assertFalse($file->fileExists(), 'File did exist to begin with.');
        $this->assertNull($file->copy($filePath . '2'), 'Copy did not return null');
    }

    public function testDeleteDeletesAFileAndReturnTrueOnSuccess()
    {
        $filePath = dirname(__FILE__) . '/_stub/fileStub';
        if (file_exists($filePath . '2')) {
            unlink($filePath . '2');
        }
        $file = new FileImpl($filePath);
        $this->assertTrue($file->fileExists(), 'File did not exist to begin with.');
        $newFile = $file->copy($filePath . '2');
        $this->assertTrue($newFile->delete(), 'Did not return true');
        $this->assertFalse($newFile->fileExists(), 'File was not deleted');

    }

    public function testDeleteReturnsFalseOnNoFile()
    {
        $filePath = dirname(__FILE__) . '/_stub/notAReadFile';
        $file = new FileImpl($filePath);
        $this->assertFalse($file->fileExists(), 'File did exist to begin with.');
        $this->assertFalse($file->delete(), 'Did not return false');
    }

    public function testDeleteReturnsFalseOnDirectoryAndCanNotDelete()
    {
        $filePath = dirname(__FILE__) . '/_stub/testFileFolder';
        if (file_exists($filePath)) {
            @unlink($filePath);
            @rmdir($filePath);
        }
        mkdir($filePath);
        $file = new FileImpl($filePath);
        $this->assertFalse($file->delete(), 'Did not return false');
        $this->assertTrue(file_exists($filePath),'Folder was deleted');
    }


    public function testDefaultAccessModeIsReadAndWritePointerAtEnd()
    {
        $filePath = dirname(__FILE__) . '/_stub/notAReadFile';
        $file = new FileImpl($filePath);
        $this->assertEquals(File::FILE_MODE_RW_POINTER_AT_END, $file->getAccessMode(), 'The file did not have the right access mode');
    }

    public function testModeIsChangeable()
    {
        $filePath = dirname(__FILE__) . '/_stub/notAReadFile';
        $file = new FileImpl($filePath);
        $file->setAccessMode(File::FILE_MODE_RW_POINTER_AT_BEGINNING);
        $this->assertEquals(File::FILE_MODE_RW_POINTER_AT_BEGINNING, $file->getAccessMode(), 'The file did not have the right access mode');

    }

    public function testModeIsOnlyAllowedModeDefinedInFileModeConstant()
    {
        $filePath = dirname(__FILE__) . '/_stub/notAReadFile';
        $file = new FileImpl($filePath);
        $file->setAccessMode('NotAValidMode');
        $this->assertEquals(File::FILE_MODE_RW_POINTER_AT_END, $file->getAccessMode(), 'The file did not have the right access mode');

    }


    public function testWriteWillWriteStringToFile()
    {
        $filePath = dirname(__FILE__) . '/_stub/fileStub';
        if (file_exists($filePath . '2')) {
            unlink($filePath . '2');
        }
        $file = new FileImpl($filePath);
        $this->assertTrue($file->fileExists(), 'File did not exist to begin with.');
        $newFile = $file->copy($filePath . '2');

        $ret = $newFile->write('test123');
        $this->assertEquals('test123', $newFile->getContents(), 'Did not write.');
        $this->assertTrue(is_int($ret), 'Did not return int');
        $this->assertGreaterThan(0, $ret, 'Did not return int greater than 0');

        $newFile->setAccessMode(File::FILE_MODE_RW_POINTER_AT_BEGINNING);
        $newFile->write('tset');
        $this->assertEquals('tset123', $newFile->getContents(), 'Did not write.');

        $newFile->setAccessMode(File::FILE_MODE_RW_TRUNCATE_FILE_TO_ZERO_LENGTH);
        $newFile->write('tteesstt');
        $this->assertEquals('tteesstt', $newFile->getContents(), 'Did not write.');


        $newFile->delete();
    }

    public function testWriteToDirectoryReturnFalse()
    {
        $fp = dirname(__FILE__) . '/_stub/testFileFolder';
        if (file_exists($fp)) {
            @unlink($fp);
            @rmdir($fp);
        }
        mkdir($fp);
        $file = new FileImpl($fp);
        $this->assertTrue(file_exists($fp), 'File did not exist to begin with.');
        $ret = $file->write('test');
        $this->assertFalse($ret, 'Did not return fasle');
        $file->delete();
    }

    public function testSizeReturnFileSize()
    {
        $filePath = dirname(__FILE__) . '/_stub/fileStub';
        if (file_exists($filePath . '2')) {
            unlink($filePath . '2');
        }
        $file = new FileImpl($filePath);
        $this->assertTrue($file->fileExists(), 'File did not exist to begin with.');
        $newFile = $file->copy($filePath . '2');

        $ret = $newFile->write('test123');
        $this->assertEquals(filesize($newFile->getAbsoluteFilePath()), $newFile->size(), 'Did not return right size.');
        $this->assertTrue(is_int($ret), 'Did not return int');

        $newFile->delete();

    }

    public function testSizeWillReturn0AsSizeOfFileNotFound()
    {
        $filePath = dirname(__FILE__) . '/_stub/notAReadFile';
        $file = new FileImpl($filePath);
        $this->assertEquals(-1, $file->size(), 'Size did not match');
    }


    public function testCopyFolderReturnsNull(){
        $filePath = dirname(__FILE__) . '/_stub/testFileFolder';
        $newPath = dirname(__FILE__) . '/_stub/_newStub';
        if (file_exists($filePath)) {
            @unlink($filePath);
            @rmdir($filePath);
        }
        if (file_exists($newPath)) {
            @unlink($newPath);
            @rmdir($newPath);
        }
        mkdir($filePath);
        $file = new FileImpl($filePath);
        $this->assertNull($file->copy($newPath), "Did not return null");
        $this->assertFalse(file_exists($newPath), 'File was copied');
    }


    public function testMoveFolderReturnsNull(){
        $filePath = dirname(__FILE__) . '/_stub/testFileFolder';
        $newPath = dirname(__FILE__) . '/_stub/_newStub';
        if (file_exists($filePath)) {
            @unlink($filePath);
            @rmdir($filePath);
        }
        if (file_exists($newPath)) {
            @unlink($newPath);
            @rmdir($newPath);
        }
        mkdir($filePath);
        $file = new FileImpl($filePath);
        $this->assertFalse($file->move($newPath), "Did not return false");
        $this->assertFalse(file_exists($newPath), 'File was copied');
    }

    public function testGetFileContentsWillReturnFalseIfDirectory(){
        $filePath = dirname(__FILE__) . '/_stub/testFileFolder';
        if (file_exists($filePath)) {
            @unlink($filePath);
            @rmdir($filePath);
        }
        mkdir($filePath);
        $file = new FileImpl($filePath);
        $this->assertFalse($file->getContents(),'Did not return false if directory');
    }

    public function testSizeOfFolderWillReturnMinus1(){
        $filePath = dirname(__FILE__) . '/_stub/testFileFolder';
        if (file_exists($filePath)) {
            @unlink($filePath);
            @rmdir($filePath);
        }
        mkdir($filePath);
        $file = new FileImpl($filePath);
        $this->assertEquals(-1,$file->size(),'Did not return false if directory');
    }

    public function testGetParentFolderWillReturnParentFolder(){
        $fn = dirname(__FILE__).'/_stub/fileStub';
        $file = new FileImpl($fn);
        $folder = $file->getParentFolder();
        $this->assertInstanceOf('Folder',$folder);
        $this->assertEquals(dirname(__FILE__).'/_stub',$folder->getAbsolutePath(),'Parent did not match');
    }


}
