<?php
require_once dirname(__FILE__) . '/../_interface/AJAXRegister.php';

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

    /**
     * @param $id string
     * @param Registrable $callback
     */
    public function registerAJAX($id, Registrable $callback)
    {
        if (!isset($this->registeredObjects[$id])) {
            $this->registeredObjects[$id] = $callback;
            $this->registeredIDs[] = $id;
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
        /** @var $callback Registrable */
        $callback = $this->registeredObjects[$id];
        return $callback->callback($id);
    }

    /**
     * @return array
     */
    public function listRegistered()
    {
        return $this->registeredIDs;
    }
}
