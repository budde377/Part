<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/27/12
 * Time: 1:11 PM
 * To change this template use File | Settings | File Templates.
 */
trait FilePathTrait
{
    protected function relativeToAbsolute($file)
    {
        if (substr($file, 0, 1) != '/') {
            $file = getcwd() . '/' . $file;
        }

        if (substr($file, -1, 1) == '/') {
            $file = substr($file, 0, strlen($file) - 1);
        }
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
     * @return string The relative path
     */
    protected function relativePath($path,$relative)
    {
        $absoluteFilePath = $this->relativeToAbsolute($path);
        $absoluteDir = $this->relativeToAbsolute($relative);
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

}
