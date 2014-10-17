<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 10/16/14
 * Time: 10:56 PM
 */

namespace ChristianBudde\cbweb\controller\json;


use ChristianBudde\cbweb\model\mail\AddressLibrary;

class MailAddressLibraryObjectImpl extends ObjectImpl{

    function __construct(AddressLibrary $addressLibrary)
    {
        parent::__construct('mail_address_library');
        $this->setVariable('addresses', $addressLibrary->listAddresses());
        $this->setVariable('catchall', $addressLibrary->hasCatchallAddress()?$addressLibrary->getCatchallAddress():null);

    }

} 