<?php
namespace ChristianBudde\Part\util\traits;
use ChristianBudde\Part\util\file\File;
use ChristianBudde\Part\util\file\FileImpl;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/27/12
 * Time: 1:11 PM
 * To change this template use File | Settings | File Templates.
 */
trait FilePathTrait
{
    protected function relativeToAbsolute($file,$currentWorkingDir = null)
    {
        if($currentWorkingDir == null){
            $currentWorkingDir = getcwd();
        }
        if (substr($file, 0, 1) != '/') {
            $file = $currentWorkingDir . '/' . $file;
        }

        if (substr($file, -1, 1) == '/') {
            $file = substr($file, 0, strlen($file) - 1);
        }
        $file = preg_replace('/\/[\/]+/','/',$file);
        $fileArray = explode('/', $file);
        $pathSizeArray = array();
        $newFile = '';
        foreach ($fileArray as $p) {
            switch ($p) {
                case '.':
                    break;
                case '':
                    $newFile .= '/';
                    break;
                case '..':
                    $size = ($v = array_pop($pathSizeArray)) === null ? 0 : $v;
                    $newFile = substr($newFile, 0, ($size + 1) * -1);
                    break;
                default:
                    $newFile .= $p . '/';
                    array_push($pathSizeArray, strlen($p));
            }
        }
        $newFile = substr($newFile, 0, -1);
        return $newFile;

    }

    /**
     * @param string $path The path that should be evaluated
     * @param string $relative The path that $path will be relative to
     * @param string | null $currentWorkingDir If null then current working dir will be gained from getcwd()
     * @return string The relative path
     */
    protected function relativePath($path,$relative,$currentWorkingDir = null)
    {
        if($currentWorkingDir == null){
            $currentWorkingDir = getcwd();
        }

        $absoluteFilePath = $this->relativeToAbsolute($path,$currentWorkingDir);
        $absoluteDir = $this->relativeToAbsolute($relative,$currentWorkingDir);
        $thisDirArray = explode('/', $absoluteFilePath);
        $newDirArray = explode('/', $absoluteDir);
        $relativePath = '';
        $sizeToCut = 0;
        foreach ($newDirArray as $k => $p) {
            if (!isset($thisDirArray[$k]) || $p != $thisDirArray[$k]) {
                $relativePath .= '../';
            } else {
                $sizeToCut += strlen($p) + 1;
            }
        }
        $relativePath .= substr($absoluteFilePath, $sizeToCut, strlen($absoluteFilePath));
        return $relativePath;
    }

    /**
     * Will generate a new file in the given path from name.
     * @param String $path
     * @param String $name
     * @param bool $hashName
     * @return File
     */
    public function newFileFromName($path, $name, $hashName = false){
        $info = pathinfo($name);
        $ext = isset($info['extension'])? ".{$info['extension']}":"";
        $name = $info['filename'];
        $n = $hashName?md5($name):$name;
        $file = new FileImpl("$path/$n$ext");
        $i = 0;
        while($file->exists()){
            $i++;
            $n = $hashName?md5($name.$i):"$name ($i)";
            $file = new FileImpl("$path/$n$ext");
        }
        return $file;

    }


}
