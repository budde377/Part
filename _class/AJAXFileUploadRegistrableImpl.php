<?php
require_once dirname(__FILE__) . '/../_interface/Registrable.php';
require_once dirname(__FILE__) . '/JSONServerImpl.php';
require_once dirname(__FILE__) . '/FileImpl.php';
require_once dirname(__FILE__) . '/ImageFileImpl.php';
require_once dirname(__FILE__) . '/../_trait/FileTrait.php';
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
        $jsonServer->registerJSONFunction(new JSONFunctionImpl('uploadImageURI', function ($fileName, $data, $sizes) use ($user) {
            $path = "/_files/{$user->getUniqueId()}/";
            $folder = new FolderImpl($_SERVER['DOCUMENT_ROOT'] . $path);
            if (!$folder->exists()) {
                $folder->create();
            }
            $file = new ImageFileImpl($this->newFileFromName($folder->getAbsolutePath(), $fileName, true)->getAbsoluteFilePath());
            $rd = @file_get_contents($data);
            if ($rd === false) {
                return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_INVALID_FILE);
            }
            $file->write($rd);
            $thumbPaths = array();
            foreach ($sizes as $size) {
                $maxHeight = $size['maxHeight'];
                $maxWidth = $size['maxWidth'];
                $minHeight = $size['minHeight'];
                $minWidth = $size['minWidth'];
                $dataURI = $size['dataURI'];
                $f = $file->copy($file->getParentFolder()->getAbsolutePath() . '/' . uniqid());
                if($f == null){
                    continue;
                }
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
                }
                if ($dataURI) {
                    $thumbPaths[] = $f->getDataURI();
                } else {
                    $nf = $f->copy($file->getParentFolder()->getAbsolutePath() . '/' . $file->getFileName() . '-S_' . $f->getWidth() . '_' . $f->getHeight() . '.' . $file->getExtension());
                    $thumbPaths[] = $path . $nf->getBaseName();
                }
                $f->delete();
            }
            $response = new JSONResponseImpl();
            $response->setPayload(array('path' => $path . $file->getBaseName(), 'thumbs' => $thumbPaths));
            return $response;
        }, array('fileName', 'data', 'sizes')));

        $jsonServer->registerJSONFunction(new JSONFunctionImpl('uploadFileURI', function ($fileName, $data) use ($user) {
            $folder = new FolderImpl($_SERVER['DOCUMENT_ROOT'] . "/_files/{$user->getUniqueId()}/");
            if (!$folder->exists()) {
                $folder->create();
            }
            $file = new FileImpl($this->newFileFromName($folder->getAbsolutePath(), $fileName, true)->getAbsoluteFilePath());
            $rd = @file_get_contents($data);
            if ($rd === false) {
                return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_INVALID_FILE);
            }
            $file->write($rd);

            $response = new JSONResponseImpl();
            $response->setPayload(array('path' => "/_files/" . $file->getRelativeFilePathTo($folder->getParentFolder()->getAbsolutePath())));
            return $response;
        }, array('fileName', 'data')));

        $jsonServer->registerJSONFunction(new JSONFunctionImpl('editImage', function ($url, $mirrorVertical, $mirrorHorizontal, $cropX, $cropY, $cropW, $cropH, $rotate, $width, $height) {
            $urlAr = explode("_files", $url);
            if(count($urlAr) != 2){
                return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_FILE_NOT_FOUND);
            }
            $file = new ImageFileImpl($_SERVER['DOCUMENT_ROOT']."/_files/{$urlAr[1]}");
            if(!$file->exists()){
                return new JSONResponseImpl(JSONResponse::RESPONSE_TYPE_ERROR, JSONResponse::ERROR_CODE_FILE_NOT_FOUND);
            }
            $newUrl = $file->getFileName();

            if($cropH != null){
                $newUrl .= "-C_{$cropX}_{$cropY}_{$cropW}_{$cropH}";
            }

            if($mirrorHorizontal || $mirrorVertical){
                $mirrorHorizontal = $mirrorHorizontal ? 1: 0;
                $mirrorVertical = $mirrorVertical? 1: 0;
                $newUrl .= "-M_{$mirrorVertical}_{$mirrorHorizontal}";
            }

            if($rotate > 0){
                $newUrl .= "-R_{$rotate}";
            }

            if($width != null && $height != null){
                $newUrl .= "-S_{$width}_{$height}";
            }
            $newUrl .= ".{$file->getExtension()}";
            $newFile = new ImageFileImpl("{$file->getParentFolder()->getAbsolutePath()}/$newUrl");
            $folderName = $file->getParentFolder()->getName();
            if($newFile->exists()){
                $result = new JSONResponseImpl();
                $result->setPayload("/_files/$folderName/{$newFile->getBaseName()}");
                return $result;
            }
            $newFile = $file->copy($newFile->getAbsoluteFilePath());
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
            $result->setPayload("/_files/$folderName/{$newFile->getBaseName()}");

            return $result;
        }, array("url","mirrorVertical","mirrorHorizontal","cropX","cropY","cropW","cropH","rotate","width","height")));

        return $jsonServer->evaluatePostInput()->getAsJSONString();

    }


}
