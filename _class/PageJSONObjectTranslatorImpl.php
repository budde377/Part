<?php
require_once dirname(__FILE__).'/../_interface/JSONObjectTranslator.php';
require_once dirname(__FILE__).'/PageJSONObjectImpl.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 24/01/13
 * Time: 08:52
 * To change this template use File | Settings | File Templates.
 */
class PageJSONObjectTranslatorImpl implements JSONObjectTranslator
{
    private $pageOrder;

    function __construct(PageOrder $pageOrder)
    {
        $this->pageOrder = $pageOrder;
    }


    /**
     * This will encode a object matching the current JSONObject into a JSONObject
     * @param Page $object
     * @return JSONObject | bool Will return JSONObject on success else FALSE
     */
    public function encode($object)
    {
        if(!($object instanceof Page)){
            return false;
        }
        $jsonObject = new PageJSONObjectImpl($object->getID(),$object->getTitle(),
            $object->getTemplate(),$object->getAlias(),$object->isHidden());

        return $jsonObject;
    }

    /**
     * This will decode an json object to
     * @param JSONObject $jsonObject
     * @return Page | bool
     */
    public function decode($jsonObject)
    {
        if(!($jsonObject instanceof JSONObject) ){
            return false;
        }

        if($jsonObject->getName() != 'page' || $jsonObject->getVariable('id') == null
        || $jsonObject->getVariable('title') == null || $jsonObject->getVariable('template') == null ||
        $jsonObject->getVariable('alias') == null){
            return false;
        }

        if(($p = $this->pageOrder->getPage($jsonObject->getVariable('id'))) == null){
            return false;
        }

        return $p;
    }


}
