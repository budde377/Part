<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/16/14
 * Time: 10:56 PM
 */

namespace ChristianBudde\cbweb\controller\json;


use ChristianBudde\cbweb\model\mail\Address;
use ChristianBudde\cbweb\model\mail\AddressLibrary;

class MailAddressLibraryObjectImpl extends ObjectImpl{

    function __construct(AddressLibrary $addressLibrary)
    {
        parent::__construct('mail_address_library');
        $l = $addressLibrary->listAddresses();
        $this->setVariable('addresses', array_combine(array_map(function (Address $k){ return $k->getLocalPart();}, $l), $l));
        $this->setVariable('catchall', $addressLibrary->hasCatchallAddress()?$addressLibrary->getCatchallAddress():null);

    }

} 