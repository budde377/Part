<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 9:06 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\controller\json\Response;
use ChristianBudde\Part\controller\json\ResponseImpl;
use ChristianBudde\Part\util\file\File;
use ChristianBudde\Part\util\file\FileImpl;
use ChristianBudde\Part\util\file\FileLibrary;
use ChristianBudde\Part\util\file\ImageFile;
use ChristianBudde\Part\util\file\ImageFileImpl;
use ChristianBudde\Part\util\file\TypeHandlerGeneratorFileImpl;
use ChristianBudde\Part\util\file\TypeHandlerGeneratorImageFileImpl;
use ChristianBudde\Part\util\traits\TypeHandlerTrait;

class FileLibraryTypeHandlerImpl extends GenericObjectTypeHandlerImpl
{

    private $container;


    use TypeHandlerTrait;

    function __construct(BackendSingletonContainer $container, FileLibrary $library)
    {
        $this->container = $container;

        parent::__construct($library, 'FileLibrary');
        $this->addFunctionAuthFunction('FileLibrary', 'uploadFile',
            $this->currentUserHasCurrentPagePrivileges($this->container));
        $this->addFunctionAuthFunction('FileLibrary', 'uploadImageFile',
            $this->currentUserHasCurrentPagePrivileges($this->container));

        $this->whitelistFunction('FileLibrary',
            'uploadFile',
            'uploadImageFile',
            'getFile',
            'getImageFile');

        $this->addFunction('FileLibrary', 'uploadFile', $this->uploadFile());
        $this->addFunction('FileLibrary', 'uploadImageFile', $this->uploadImageFile());
        $this->addFunction('FileLibrary', 'getFile', $this->getFile());
        $this->addFunction('FileLibrary', 'getImageFile', $this->getImageFile());

    }

    private function uploadFile()
    {
        return function (FileLibrary $library, array $fileArray) {
            //TODO test
            $file = $library->uploadToLibrary($this->container->getUserLibraryInstance()->getUserLoggedIn(), $fileArray);
            if ($file == null) {
                return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_COULD_NOT_CREATE_FILE);
            }
            return $this->filePath($file);
        };
    }

    private function uploadImageFile()
    {
        return function (FileLibrary $library, array $fileArray, array $sizes) {
            //TODO test
            $file = $library->uploadToLibrary($this->container->getUserLibraryInstance()->getUserLoggedIn(), $fileArray);
            if ($file == null) {
                return new ResponseImpl(Response::RESPONSE_TYPE_ERROR, Response::ERROR_CODE_COULD_NOT_CREATE_FILE);
            }

            $file = $this->wrapImageFile($file);

            $result = [];
            foreach ($sizes as $key => $val) {
                $newFile = $this->fileFromScaleMethod($file, $val);
                if ($newFile == null) {
                    continue;
                }
                if ($val["dataURI"]) {
                    $result[$key] = $newFile->getDataURI();
                } else {
                    $result[$key] = $this->filePath($newFile);;
                }
            }
            return ["path" => $this->filePath($file), "sizes" => $result];
        };
    }

    private function getFile()
    {
        return function (FileLibrary $library, $file_name) {

            $file = new FileImpl($library->getFilesFolder()->getAbsolutePath().'/'.$file_name);
            if($file->exists() && $library->containsFile($file)){
                return $this->wrapFile($file);
            }

            if(strpos($file_name,"/") !== false){
                return null;
            }

            $fileList = $library->getFileList();
            foreach ($fileList as $file) {
                if ($file->getFilename() == $file_name) {
                    return $this->wrapFile($file);
                }
            }
            return null;
        };
    }

    private function getImageFile()
    {
        return function (FileLibrary $library, $file_name) {
            $fileFinder = $this->getFile();
            /** @var File $file */
            $file = $fileFinder($library, $file_name);
            if ($file == null) {
                return null;
            }

            return $this->wrapImageFile($file);


        };

    }

    private function fileFromScaleMethod(ImageFile $file, $scaleArray)
    {

        if (!is_array($scaleArray) || !isset($scaleArray["height"], $scaleArray["width"], $scaleArray["scaleMethod"], $scaleArray["dataURI"])) {
            return null;
        }
        $width = $scaleArray["width"];
        $height = $scaleArray["height"];
        $scaleMethod = $scaleArray['scaleMethod'];
        switch ($scaleMethod) {
            case 0:
                $newFile = $file->forceSize($width, $height, true);
                break;
            case 1:
                $newFile = $file->scaleToWidth($width, true);
                break;
            case 2:
                $newFile = $file->scaleToHeight($height, true);
                break;
            case 3:
                $newFile = $file->scaleToInnerBox($width, $height, true);
                break;
            case 4:
                $newFile = $file->scaleToOuterBox($width, $height, true);
                break;
            case 5:
                $newFile = $file->limitToInnerBox($width, $height, true);
                break;
            case 6:
                $newFile = $file->extendToInnerBox($width, $height, true);
                break;
            case 7:
                $newFile = $file->limitToOuterBox($width, $height, true);
                break;
            case 8:
                $newFile = $file->extendToOuterBox($width, $height, true);
                break;
            default:
                $newFile = null;
        }
        return $newFile;
    }

    private function wrapImageFile(File $file)
    {
        return new TypeHandlerGeneratorImageFileImpl(
            $this->container->getTypeHandlerLibraryInstance(),
            new ImageFileImpl($file->getAbsoluteFilePath()));
    }

    private function wrapFile(File $file)
    {
        return new TypeHandlerGeneratorFileImpl(
            $this->container->getTypeHandlerLibraryInstance(),
            $file);
    }


    private function filePath(File $file){
        return $file->getParentFolder()->getName().'/'.$file->getFilename();
    }

}