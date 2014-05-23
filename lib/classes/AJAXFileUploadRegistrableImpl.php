<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 7/10/13
 * Time: 2:07 PM
 * To change this template use File | Settings | File Templates.
 */

class AJAXFileUploadRegistrableImpl implements Registrable
{
    use FileTrait;

    private $container;

    function __construct(BackendSingletonContainer $container)
    {
        $this->container = $container;
    }


    /**
     * @param $id string
     * @return string | null Will return string if id is found, else null
     */
    public function callback($id)
    {

        $currentPage = $this->container->getCurrentPageStrategyInstance()->getCurrentPage();
        $user = $this->container->getUserLibraryInstance()->getUserLoggedIn();
        if ($user == null || !$user->getUserPrivileges()->hasPagePrivileges($currentPage)) {
            return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_UNAUTHORIZED);
        }

        $jsonServer = new JSONServerImpl();

        $fileLibrary = $this->container->getFileLibraryInstance();

        $config = $this->container->getConfigInstance();

        $jsonServer->registerJSONFunction(new JSONFunctionImpl('uploadImageURI', function ($fileName, $data, $sizes) use ($user, $fileLibrary, $config) {


            $rd = @file_get_contents($data);
            if ($rd === false) {
                return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_INVALID_FILE);
            }
            $tmpFile = new ImageFileImpl($this->tmpFilePath().$fileName);
            $tmpFile->write($rd);
            $file = $fileLibrary->addToLibrary($user, $tmpFile);
            $tmpFile->delete();
            if($file == null){
                return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_COULD_NOT_CREATE_FILE);
            }
            $file = new ImageFileImpl($file->getAbsoluteFilePath());
            $thumbPaths = array();
            $path = '_files/' . $file->getParentFolder()->getName()."/";
/*
            static const SCALE_METHOD_EXACT = 0;
            static const SCALE_METHOD_EXACT_WIDTH = 1;
            static const SCALE_METHOD_EXACT_HEIGHT = 2;
            static const SCALE_METHOD_PRECISE_INNER_BOX = 3;
            static const SCALE_METHOD_PRECISE_OUTER_BOX = 4;
            static const SCALE_METHOD_LIMIT_TO_INNER_BOX = 5;
            static const SCALE_METHOD_EXTEND_TO_INNER_BOX = 6;
            static const SCALE_METHOD_LIMIT_TO_OUTER_BOX = 7;
            static const SCALE_METHOD_EXTEND_TO_OUTER_BOX = 8;*/


            foreach ($sizes as $size) {

                $scaleMethod = $size['scaleMethod'];
                $width = $size['width'];
                $height = $size['height'];
                $dataURI = $size['dataURI'];

                $f = $file->copy($this->tmpFilePath());
                if($f == null){
                    continue;
                }

                switch($scaleMethod){
                    case 0;
                        $f->forceSize($width, $height);
                        break;
                    case 1;
                        $f->scaleToWidth($width);
                        break;
                    case 2;
                        $f->scaleToHeight($height);
                        break;
                    case 3;
                        $f->scaleToInnerBox($width, $height);
                        break;
                    case 4;
                        $f->scaleToOuterBox($width, $height);
                        break;
                    case 5;
                        $f->limitToInnerBox($width, $height);
                        break;
                    case 6;
                        $f->extendToInnerBox($width, $height);
                        break;
                    case 7;
                        $f->limitToOuterBox($width, $height);
                        break;
                    case 8;
                        $f->extendToOuterBox($width, $height);
                        break;


                }
/*
                if ($maxHeight >= 0 && $maxWidth >= 0 && $minWidth < 0 && $minHeight < 0) {
                    $f->limitToOuterBox($maxWidth, $maxHeight);
                } else if ($maxHeight < 0 && $maxWidth < 0 && $minWidth >= 0 && $minHeight >= 0) {
                    $f->extendToInnerBox($minWidth, $minHeight);
                } else if ($maxHeight >= 0 && $maxWidth < 0 && $minWidth < 0 && $minHeight == $maxHeight) {
                    $f->scaleToHeight($maxHeight);
                } else if ($maxHeight < 0 && $maxWidth >= 0 && $minWidth == $maxWidth && $minHeight < 0) {
                    $f->scaleToWidth($minWidth);
                } else if ($maxHeight >= 0 && $maxWidth >= 0 && $minWidth == $maxWidth && $minHeight == $maxHeight) {
                    $f->forceSize($maxWidth, $maxHeight);
                }*/
                if ($dataURI) {
                    $thumbPaths[] = $f->getDataURI();
                } else {
                    $nf = $fileLibrary->addVersionOfFile($file, $f , 'S_' . $f->getWidth() . '_' . $f->getHeight());
                    $thumbPaths[] = $path. $nf->getFilename();
                }
                $f->delete();
            }
            $response = new JSONResponseImpl();
            $response->setPayload(array('path' => $path . $file->getFilename(), 'thumbs' => $thumbPaths));
            return $response;
        }, array('fileName', 'data', 'sizes')));

        $jsonServer->registerJSONFunction(new JSONFunctionImpl('uploadFileURI', function ($fileName, $data) use ($user, $fileLibrary) {
            $tmpFile = new FileImpl($this->tmpFilePath().$fileName);
            $rd = @file_get_contents($data);
            if ($rd === false) {
                return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_INVALID_FILE);
            }
            $tmpFile->write($rd);
            $file = $fileLibrary->addToLibrary($user, $tmpFile);
            $response = new JSONResponseImpl();
            $response->setPayload(array('path' => "/_files/" . $file->getParentFolder()->getName()."/".$file->getFilename()));
            return $response;
        }, array('fileName', 'data')));

        $jsonServer->registerJSONFunction(new JSONFunctionImpl('editImage', function ($url, $mirrorVertical, $mirrorHorizontal, $cropX, $cropY, $cropW, $cropH, $rotate, $width, $height) use ($fileLibrary){
            $urlAr = explode("_files", $url);
            if(count($urlAr) != 2){
                return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_FILE_NOT_FOUND);
            }
            $file = new ImageFileImpl($fileLibrary->getFilesFolder()->getAbsolutePath()."/{$urlAr[1]}");
            if(($file = $fileLibrary->findOriginalFileToVersion($file)) == null){
                return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_FILE_NOT_FOUND);
            }


            $version = array();

            if($cropH != null){
                $version[] = "C_{$cropX}_{$cropY}_{$cropW}_{$cropH}";
            }

            if($mirrorHorizontal || $mirrorVertical){
                $mirrorHorizontal = $mirrorHorizontal ? 1: 0;
                $mirrorVertical = $mirrorVertical? 1: 0;
                $version[] = "M_{$mirrorVertical}_{$mirrorHorizontal}";
            }

            if($rotate > 0){
                $version[] = "R_{$rotate}";
            }

            if($width != null && $height != null){
                $version[] = "S_{$width}_{$height}";
            }

            $version = implode("-", $version);
            $folderName = $file->getParentFolder()->getName();

            if(($newFile = $fileLibrary->findVersionOfFile($file, $version)) != null){
                $result = new JSONResponseImpl();
                $result->setPayload("/_files/$folderName/{$newFile->getFilename()}");
                return $result;
            }
            $newFile = $fileLibrary->addVersionOfFile($file, $file, $version);

            $newFile = new ImageFileImpl($newFile->getAbsoluteFilePath());

            if($width != null && $height != null){
                $newFile->forceSize($width, $height);
            }


            if($cropH != null){
                $newFile->crop($cropX, $cropY, $cropW, $cropH);
            }


            if($rotate > 0){
                $newFile->rotate(90*$rotate);
            }
            if($mirrorHorizontal){
                $newFile->mirrorHorizontal();
            }

            if($mirrorVertical){
                $newFile->mirrorVertical();
            }

            $result = new JSONResponseImpl();
            $result->setPayload("/_files/$folderName/{$newFile->getFilename()}");

            return $result;
        }, array("url","mirrorVertical","mirrorHorizontal","cropX","cropY","cropW","cropH","rotate","width","height")));

        return $jsonServer->evaluatePostInput()->getAsJSONString();

    }


    private function tmpFilePath(){
        return $this->container->getConfigInstance()->getTmpFolderPath()."/".uniqid("tmpfile");
    }

}
