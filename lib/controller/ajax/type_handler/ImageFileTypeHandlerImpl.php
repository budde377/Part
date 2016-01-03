<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 3/3/15
 * Time: 9:24 PM
 */

namespace ChristianBudde\Part\controller\ajax\type_handler;


use ChristianBudde\Part\BackendSingletonContainer;
use ChristianBudde\Part\util\file\ImageFile;
use ChristianBudde\Part\util\traits\TypeHandlerTrait;

class ImageFileTypeHandlerImpl extends FileTypeHandlerImpl
{

    use TypeHandlerTrait;

    private $container;

    function __construct(BackendSingletonContainer $container, ImageFile $file)
    {
        $this->container = $container;
        parent::__construct($container, $file);
        $this->whitelistType('ImageFile');


        $this->addFunctionPreCallFunction('ImageFile', 'crop', $f = $this->createSpliceAndTrueEndPreFunction(4));
        $this->addFunctionPreCallFunction('ImageFile', 'forceSize', $f = $this->createSpliceAndTrueEndPreFunction(2));
        $this->addFunctionPreCallFunction('ImageFile', 'scaleToInnerBox', $f);
        $this->addFunctionPreCallFunction('ImageFile', 'scaleToOuterBox', $f);
        $this->addFunctionPreCallFunction('ImageFile', 'limitToInnerBox', $f);
        $this->addFunctionPreCallFunction('ImageFile', 'limitToOuterBox', $f);
        $this->addFunctionPreCallFunction('ImageFile', 'extendToInnerBox', $f);
        $this->addFunctionPreCallFunction('ImageFile', 'extendToOuterBox', $f);
        $this->addFunctionPreCallFunction('ImageFile', 'scaleToWidth', $f = $this->createSpliceAndTrueEndPreFunction(1));
        $this->addFunctionPreCallFunction('ImageFile', 'scaleToHeight', $f);
        $this->addFunctionPreCallFunction('ImageFile', 'rotate', $f);
        $this->addFunctionPreCallFunction('ImageFile', 'mirrorHorizontal', $f = $this->createSpliceAndTrueEndPreFunction(0));
        $this->addFunctionPreCallFunction('ImageFile', 'mirrorVertical', $f);

        /** @noinspection PhpUnusedParameterInspection */
        $this->addTypeAuthFunction('ImageFile', function ($type, $instance, $function)  {
            return
                !in_array($function, ['scaleToWidth',
                    'scaleToHeight',
                    'scaleToInnerBox',
                    'scaleToOuterBox',
                    'limitToInnerBox',
                    'limitToOuterBox',
                    'extendToInnerBox',
                    'extendToOuterBox',
                    'forceSize',
                    'crop',
                    'rotate',
                    'mirrorVertical',
                    'mirrorHorizontal']) ||
                $this->currentUserHasCurrentPagePrivileges($this->container);
        });
    }

    private function createSpliceAndTrueEndPreFunction($length)
    {
        /** @noinspection PhpUnusedParameterInspection
         * @param $type
         * @param $instance
         * @param $functionName
         * @param $arguments
         */
        return function ($type, $instance, $functionName, &$arguments) use ($length) {
            $arguments = array_splice($arguments, 0, $length);
            $arguments[$length] = true;
        };
    }
}