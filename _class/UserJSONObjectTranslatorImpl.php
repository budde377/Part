<?php
require_once dirname(__FILE__).'/../_interface/JSONObjectTranslator.php';
require_once dirname(__FILE__).'/UserJSONObjectImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 24/01/13
 * Time: 10:34
 * To change this template use File | Settings | File Templates.
 */
class UserJSONObjectTranslatorImpl implements JSONObjectTranslator
{
    private $userLibrary;
    private $userPrivileges;

    function __construct(UserLibrary $userLibrary, UserPrivilegesLibrary $userPrivileges)
    {
        $this->userLibrary = $userLibrary;
        $this->userPrivileges = $userPrivileges;
    }


    /**
     * This will encode a object matching the current JSONObject into a JSONObject
     * @param User $object
     * @return JSONObject | bool Will return JSONObject on success else FALSE
     */
    public function encode($object)
    {
        if(!($object instanceof User)){
            return false;
        }
        $privilege = $this->userPrivileges->getUserPrivileges($object);
        $p = $privilege->hasRootPrivileges()?'root':($privilege->hasSitePrivileges()?'site':'page');
        $jsonUser = new UserJSONObjectImpl($object->getUsername(),$object->getMail(),$p, $object->getParent());

        return $jsonUser;
    }

    /**
     * This will decode an json object to
     * @param JSONObject $jsonObject
     * @return User | bool
     */
    public function decode($jsonObject)
    {
        if(!($jsonObject instanceof JSONObject)){
            return false;
        }

        if($jsonObject->getName() != 'user' || ($username = $jsonObject->getVariable('username')) == null || $jsonObject->getVariable('mail') == null){
            return false;
        }

        if(($u = $this->userLibrary->getUser($username)) == null){
            return false;
        }

        return $u;
    }
}
