<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/13/12
 * Time: 6:09 PM
 * To change this template use File | Settings | File Templates.
 */
class AJAXRegisterImpl implements AJAXRegister
{

    private $registeredIDs = array();
    private $registeredObjects = array();
    private $container;

    function __construct(BackendSingletonContainer $container)
    {
        $this->container = $container;
    }

    /**
     * @param $id string
     * @param Registrable $callback
     */
    public function registerAJAX($id, Registrable $callback)
    {

        if (!isset($this->registeredObjects[$id])) {
            $this->registeredObjects[$id] = array($callback);
            $this->registeredIDs[] = $id;
        } else {
            array_push($this->registeredObjects[$id], $callback);
        }
    }


    /**
     * @param $id string
     * @return string | null
     */
    public function getAJAXFromRegistered($id)
    {
        if (!isset($this->registeredObjects[$id])) {
            return null;
        }
        $callbacks = $this->registeredObjects[$id];
        $ret = null;
        while (count($callbacks) > 0 && $ret == null) {
            $callback = array_splice($callbacks, 0, 1);
            $ret = $callback[0]->callback($id);
        }
        return $ret;
    }

    /**
     * @return array
     */
    public function listRegistered()
    {
        return $this->registeredIDs;
    }

    /**
     * @param Config $config
     * @throws ClassNotDefinedException
     * @throws FileNotFoundException
     * @throws ClassNotInstanceOfException
     * @return void
     */
    public function registerAJAXFromConfig(Config $config)
    {
        $registrable = $config->getAJAXRegistrable();
        foreach ($registrable as $fileArray) {
            $className = $fileArray['class_name'];
            $path = $fileArray['path'];
            $ajaxId = $fileArray['ajax_id'];
            if (!file_exists($path)) {
                throw new FileNotFoundException($path);
            }

            require_once $path;

            if (!class_exists($className)) {
                throw new ClassNotDefinedException($className);
            }
            $instance = new $className($this->container);

            if (!($instance instanceof Registrable)) {
                throw new ClassNotInstanceOfException($className,"Registrable");
            }

            $this->registerAJAX($ajaxId, $instance);
        }
    }

    /**
     * Returns the result of callback() function on the registrable object.
     * ID calculated from function name such as Page.[fn](..)
     * @param string $functionName
     * @return string | null Will return null if id is not found else string
     */
    public function getAJAXFromRegisteredFromFunctionName($functionName)
    {
        $idAr = explode('.', $functionName);
        return $this->getAJAXFromRegistered(is_array($idAr) && count($idAr) > 0?$idAr[0]:null);
    }
}
