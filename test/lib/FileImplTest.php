<?php

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/1/12
 * Time: 9:38 PM
 */
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\util\file\FileImpl;
use ChristianBudde\cbweb\util\file\File;
use ChristianBudde\cbweb\controller\json\FileObjectImpl;
use ChristianBudde\cbweb\util\traits\FilePathTrait;
use PHPUnit_Framework_TestCase;

class FileImplTest extends PHPUnit_Framework_TestCase
{
    use FilePathTrait;

    public function testFileExistsReturnFalseOnFileNotFound()
    {
        $file = new FileImpl('NotAnExistingFile');
        $this->assertFalse($file->exists(), 'Did not return false on non-existing file');
    }

    public function testFileExistsReturnTrueOnFileExists()
    {
        $filePath = dirname(__FILE__) . '/../stubs/templateStub.twig';
        $file = new FileImpl($filePath);
        $this->assertTrue($file->exists(), 'Did not return true on existing file');
    }

    public function testFileExistReturnFalseOnDirectory()
    {
        $filePath = dirname(__FILE__) . '/../stubs';
        $file = new FileImpl($filePath);
        $this->assertFalse($file->exists(), 'Did not return false on directory');
    }

    public function testGetContentsReturnEmptyStringOnFileNotFound()
    {
        $file = new FileImpl('NotAnExistingFile');
        $this->assertTrue('' === $file->getContents(), 'Did not return empty string on file not found');
    }

    public function testGetContentsReturnContentOnFileFound()
    {
        $filePath = dirname(__FILE__) . '/../stubs/templateStub.twig';
        $file = new FileImpl($filePath);
        $this->assertEquals(file_get_contents($filePath), $file->getContents(), 'Content did not match');
    }


    public function testGetAbsolutePathReturnAbsolutePathToFile()
    {
        $filePath = dirname(__FILE__) . '/.././stubs/templateStub.twig';
        $altFilePath = $this->relativeToAbsolute(dirname(__FILE__).'/../stubs/templateStub.twig');
        $file = new FileImpl($filePath);
        $this->assertEquals( $altFilePath, $file->getAbsoluteFilePath(),
            'Did not return right absolute file path');
    }

    public function testGetRelativePathReturnRelativePathToFile()
    {
        $file = dirname(__FILE__) . '/someFile';
        $relativeDir = dirname(__FILE__) . '/../class';
        $expected = '../lib/someFile';
        $file = new FileImpl($file);
        $this->assertEquals($expected, $file->getRelativeFilePathTo($relativeDir), 'Paths did not match');
    }

    public function testGetBaseNameReturnsBaseName()
    {
        $file = 'someFile';
        $path = dirname(__FILE__) . '/' . $file;
        $f = new FileImpl($path);
        $this->assertEquals($file, $f->getFilename(), 'Did not return expected BaseName');
    }

    public function testGetBaseNameReturnsBaseNameIfFileIsDir()
    {
        $file = 'someFile';
        $path = dirname(__FILE__) . '/' . $file . '/';
        $f = new FileImpl($path);
        $this->assertEquals($file, $f->getFilename(), 'Did not return expected BaseName');
    }

    public function testGetFilenameReturnsFileName()
    {
        $file = 'someFile.test';
        $path = dirname(__FILE__) . '/' . $file;
        $f = new FileImpl($path);
        $this->assertEquals('someFile', $f->getBasename(), 'Did not return expected FileName');

    }

    public function testGetFileNameReturnsFileNameIfFileIsDir()
    {
        $file = 'someFile.test';
        $path = dirname(__FILE__) . '/' . $file . '/';
        $f = new FileImpl($path);
        $this->assertEquals('someFile', $f->getBasename(), 'Did not return expected FileName');

    }


    public function testGetExtensionReturnsExtension()
    {
        $file = 'someFile.test';
        $path = dirname(__FILE__) . '/' . $file;
        $f = new FileImpl($path);
        $this->assertEquals('test', $f->getExtension(), 'Did not return expected FileName');

    }

    public function testGetExtensionReturnsExtensionIfFileIsDir()
    {
        $file = 'someFile.test';
        $path = dirname(__FILE__) . '/' . $file . '/';
        $f = new FileImpl($path);
        $this->assertEquals('test', $f->getExtension(), 'Did not return expected FileName');

    }

    public function testGetExtensionReturnsNoExtensionIfAbsent()
    {
        $file = 'someFile';
        $path = dirname(__FILE__) . '/' . $file . '/';
        $f = new FileImpl($path);
        $this->assertEquals('', $f->getExtension(), 'Did not return expected FileName');

    }

    public function testMoveMovesAndReturnTrueOnSuccess()
    {
        $filePath = $this->relativeToAbsolute(dirname(__FILE__) . '/../stubs/fileStub');
        $file = new FileImpl($filePath);
        $this->assertTrue($file->exists(), 'File did not exist to begin with.');
        $this->assertTrue($file->move($filePath . '2'), 'Move did not return true');
        $this->assertEquals($filePath . '2', $file->getAbsoluteFilePath(), 'The path was not updated.');
        $this->assertTrue($file->exists(), 'The file was not moved');
        $file->move($filePath);
    }

    public function testMoveReturnFalseOnFileNotFound()
    {
        $filePath = $this->relativeToAbsolute(dirname(__FILE__) . '/../stubs/notAReadFile');
        $file = new FileImpl($filePath);
        $this->assertFalse($file->exists(), 'File did exist to begin with.');
        $this->assertFalse($file->move($filePath . '2'), 'Move did return true');
        $this->assertEquals($filePath, $file->getAbsoluteFilePath(), 'The path was updated.');

    }

    public function testCopyCopiesAndReturnFileOnSuccess()
    {
        $filePath = $this->relativeToAbsolute(dirname(__FILE__) . '/../stubs/fileStub');
        $file = new FileImpl($filePath);
        $this->assertTrue($file->exists(), 'File did not exist to begin with.');
        $newFile = $file->copy($filePath . '2');
        $this->assertInstanceOf('ChristianBudde\cbweb\util\file\File', $newFile, 'Did not return an instance of File');
        $this->assertEquals($filePath . '2', $newFile->getAbsoluteFilePath(), 'New file did not have right path');
        $this->assertTrue($newFile->exists(), 'The new file did note exists');
        unlink($newFile->getAbsoluteFilePath());
    }

    public function testCopyReturnNullOnNoFile()
    {
        $filePath = dirname(__FILE__) . '/../stubs/notAReadFile';
        $file = new FileImpl($filePath);
        $this->assertFalse($file->exists(), 'File did exist to begin with.');
        $this->assertNull($file->copy($filePath . '2'), 'Copy did not return null');
    }

    public function testDeleteDeletesAFileAndReturnTrueOnSuccess()
    {
        $filePath = dirname(__FILE__) . '/../stubs/fileStub';
        if (file_exists($filePath . '2')) {
            unlink($filePath . '2');
        }
        $file = new FileImpl($filePath);
        $this->assertTrue($file->exists(), 'File did not exist to begin with.');
        $newFile = $file->copy($filePath . '2');
        $this->assertTrue($newFile->delete(), 'Did not return true');
        $this->assertFalse($newFile->exists(), 'File was not deleted');

    }

    public function testDeleteReturnsFalseOnNoFile()
    {
        $filePath = dirname(__FILE__) . '/../stubs/notAReadFile';
        $file = new FileImpl($filePath);
        $this->assertFalse($file->exists(), 'File did exist to begin with.');
        $this->assertFalse($file->delete(), 'Did not return false');
    }

    public function testDeleteReturnsFalseOnDirectoryAndCanNotDelete()
    {
        $filePath = dirname(__FILE__) . '/../stubs/testFileFolder';
        if (file_exists($filePath)) {
            @unlink($filePath);
            @rmdir($filePath);
        }
        mkdir($filePath);
        $file = new FileImpl($filePath);
        $this->assertFalse($file->delete(), 'Did not return false');
        $this->assertTrue(file_exists($filePath), 'Folder was deleted');
    }


    public function testDefaultAccessModeIsReadAndWritePointerAtEnd()
    {
        $filePath = dirname(__FILE__) . '/../stubs/notAReadFile';
        $file = new FileImpl($filePath);
        $this->assertEquals(File::FILE_MODE_RW_POINTER_AT_END, $file->getAccessMode(), 'The file did not have the right access mode');
    }

    public function testModeIsChangeable()
    {
        $filePath = dirname(__FILE__) . '/../stubs/notAReadFile';
        $file = new FileImpl($filePath);
        $file->setAccessMode(File::FILE_MODE_RW_POINTER_AT_BEGINNING);
        $this->assertEquals(File::FILE_MODE_RW_POINTER_AT_BEGINNING, $file->getAccessMode(), 'The file did not have the right access mode');

    }

    public function testModeIsOnlyAllowedModeDefinedInFileModeConstant()
    {
        $filePath = dirname(__FILE__) . '/../stubs/notAReadFile';
        $file = new FileImpl($filePath);
        $file->setAccessMode('NotAValidMode');
        $this->assertEquals(File::FILE_MODE_RW_POINTER_AT_END, $file->getAccessMode(), 'The file did not have the right access mode');

    }


    public function testWriteWillWriteStringToFile()
    {
        $filePath = dirname(__FILE__) . '/../stubs/fileStub';
        if (file_exists($filePath . '2')) {
            unlink($filePath . '2');
        }
        $file = new FileImpl($filePath);
        $this->assertTrue($file->exists(), 'File did not exist to begin with.');
        $newFile = $file->copy($filePath . '2');

        $ret = $newFile->write('test123');
        $this->assertEquals('fileStubtest123', $newFile->getContents(), 'Did not write.');
        $this->assertTrue(is_int($ret), 'Did not return int');
        $this->assertGreaterThan(0, $ret, 'Did not return int greater than 0');

        $newFile->setAccessMode(File::FILE_MODE_RW_POINTER_AT_BEGINNING);
        $newFile->write('tset');
        $this->assertEquals('tsetStubtest123', $newFile->getContents(), 'Did not write.');

        $newFile->setAccessMode(File::FILE_MODE_RW_TRUNCATE_FILE_TO_ZERO_LENGTH);
        $newFile->write('tteesstt');
        $this->assertEquals('tteesstt', $newFile->getContents(), 'Did not write.');


        $newFile->delete();
    }

    public function testWriteToDirectoryReturnFalse()
    {
        $fp = dirname(__FILE__) . '/../stubs/testFileFolder';
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
        $filePath = dirname(__FILE__) . '/../stubs/fileStub';
        if (file_exists($filePath . '2')) {
            unlink($filePath . '2');
        }
        $file = new FileImpl($filePath);
        $this->assertTrue($file->exists(), 'File did not exist to begin with.');
        $newFile = $file->copy($filePath . '2');

        $ret = $newFile->write('test123');
        $this->assertEquals(filesize($newFile->getAbsoluteFilePath()), $newFile->size(), 'Did not return right size.');
        $this->assertTrue(is_int($ret), 'Did not return int');

        $newFile->delete();

    }

    public function testSizeWillReturn0AsSizeOfFileNotFound()
    {
        $filePath = dirname(__FILE__) . '/../stubs/notAReadFile';
        $file = new FileImpl($filePath);
        $this->assertEquals(-1, $file->size(), 'Size did not match');
    }


    public function testCopyFolderReturnsNull()
    {
        $filePath = dirname(__FILE__) . '/../stubs/testFileFolder';
        $newPath = dirname(__FILE__) . '/../stubs/_newStub';
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


    public function testMoveFolderReturnsNull()
    {
        $filePath = dirname(__FILE__) . '/../stubs/testFileFolder';
        $newPath = dirname(__FILE__) . '/../stubs/_newStub';
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

    public function testGetFileContentsWillReturnFalseIfDirectory()
    {
        $filePath = dirname(__FILE__) . '/../stubs/testFileFolder';
        if (file_exists($filePath)) {
            @unlink($filePath);
            @rmdir($filePath);
        }
        mkdir($filePath);
        $file = new FileImpl($filePath);
        $this->assertFalse($file->getContents(), 'Did not return false if directory');
    }

    public function testSizeOfFolderWillReturnMinus1()
    {
        $filePath = dirname(__FILE__) . '/../stubs/testFileFolder';
        if (file_exists($filePath)) {
            @unlink($filePath);
            @rmdir($filePath);
        }
        mkdir($filePath);
        $file = new FileImpl($filePath);
        $this->assertEquals(-1, $file->size(), 'Did not return false if directory');
    }

    public function testGetParentFolderWillReturnParentFolder()
    {
        $fn = $this->relativeToAbsolute(dirname(__FILE__) . '/../stubs/fileStub');
        $folderNo = $this->relativeToAbsolute(dirname(__FILE__) . '/../stubs');
        $file = new FileImpl($fn);
        $folder = $file->getParentFolder();
        $this->assertInstanceOf('ChristianBudde\cbweb\util\file\Folder', $folder);
        $this->assertEquals($folderNo, $folder->getAbsolutePath(), 'Parent did not match');
    }


    public function testGetResourceWillReturnFileResource()
    {
        $fn = dirname(__FILE__) . '/../stubs/fileStub';
        $file = new FileImpl($fn);
        $resType = @get_resource_type($file->getResource());
        $this->assertTrue('file' == $resType || $resType == 'stream', 'Did not return resource of right type');
    }

    public function testGetMimeTypeWillReturnMimeType()
    {
        $fn = dirname(__FILE__) . '/../stubs/fileStub';
        $file = new FileImpl($fn);
        $this->assertEquals("text/plain", $file->getMimeType());

    }

    public function testGetMimeTypeWillReturnNullOnNoFile()
    {
        $fn = dirname(__FILE__) . '/../stubs/nonExistingFile';
        $file = new FileImpl($fn);
        $this->assertNull($file->getMimeType());

    }

    public function testGetDataURIWillReturnNullOnNoFile()
    {
        $fn = dirname(__FILE__) . '/../stubs/nonExistingFile';
        $file = new FileImpl($fn);
        $this->assertNull($file->getDataURI());
    }

    public function testGetDataURIWillReturnURIOnFile()
    {
        $fn = dirname(__FILE__) . '/../stubs/imageFileStub300x200.png';
        $file = new FileImpl($fn);
        $this->assertNotNull($file->getDataURI());
        $this->assertStringStartsWith("data:{$file->getMimeType()};base64,", $file->getDataURI());
    }


    public function testModificationTimeAndCreationTimeAreRight()
    {
        $fn = dirname(__FILE__) . '/../stubs/fileStub';
        $file = new FileImpl($fn);
        $this->assertEquals(filemtime($fn), $file->getModificationTime());
        $this->assertEquals(filectime($fn), $file->getCreationTime());
    }

    public function testModificationTimeAndCreationTimeOfNonExistingIsOK()
    {
        $fn = dirname(__FILE__) . '/../stubs/nonExistingFile';
        $file = new FileImpl($fn);
        $this->assertEquals(0, $file->getModificationTime());
        $this->assertEquals(0, $file->getCreationTime());
    }

    public function testJSONObjectIsRight()
    {
        $fn = dirname(__FILE__) . '/../stubs/fileStub';
        $file = new FileImpl($fn);

        $o = $file->jsonObjectSerialize();
        $this->assertEquals(new FileObjectImpl($file), $o);
    }

}
