<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/16/14
 * Time: 10:56 PM
 */

namespace ChristianBudde\Part\controller\json;


use ChristianBudde\Part\model\mail\Address;
use ChristianBudde\Part\model\mail\AddressLibrary;

class MailAddressLibraryObjectImpl extends ObjectImpl{

    function __construct(AddressLibrary $addressLibrary)
    {
        parent::__construct('mail_address_library');
        $l = $addressLibrary->listAddresses();
        $this->setVariable('addresses', array_combine(array_map(function (Address $k){ return $k->getLocalPart();}, $l), $l));
        $this->setVariable('catchall', $addressLibrary->hasCatchallAddress()?$addressLibrary->getCatchallAddress():null);

    }

} 