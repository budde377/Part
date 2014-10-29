<?php
namespace ChristianBudde\cbweb\view\page_element;

use ChristianBudde\cbweb\BackendSingletonContainer;
use ChristianBudde\cbweb\model\mail\Address;
use ChristianBudde\cbweb\model\mail\Domain;


/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/13/14
 * Time: 9:29 AM
 */
class UserSettingsEditMailPageElementImpl extends PageElementImpl
{
    private $backendContainer;
    private $mailDomainLibrary;
    private $currentUser;


    function __construct(BackendSingletonContainer $backendContainer)
    {
        $this->backendContainer = $backendContainer;
        $this->mailDomainLibrary = $backendContainer->getMailDomainLibraryInstance();
        $this->currentUser = $backendContainer->getUserLibraryInstance()->getUserLoggedIn();
    }


    public function generateContent()
    {

        $userForm = $this->getPageUserList();

        $userForm = $userForm ==""?"":"
            <label>
                Vælg brugere
            </label>
            <ul class='owner_check_list'>
                $userForm
            </ul>";


        $out = "
        <h3>Domæner</h3>
        <ul class='floating_list has_deletable' id='UserSettingsEditMailDomainList'>
            {$this->getDomainList()}
        </ul>
        <form id='UserSettingsEditMailAddDomainForm' class='mail_form expandable' data-function-string='MailDomainLibrary.createDomain(domain_name,super_password)'>
            <div>
            <label>
                Domæne
                <input type='text' name='domain_name'/>
            </label>
            <label>
                Super-kodeord
                <input type='password' name='super_password'/>
            </label>

            <div class='submit'>
            <input type='submit' value='Opret Alias'/>
            </div>
            </div>
        </form>
        <h3>Domæne alias</h3>
        <ul class='floating_list points_to has_deletable' id='UserSettingsEditMailDomainAliasList'>
            {$this->getMailAliasList()}
        </ul>
        <form id=\"UserSettingsEditMailAddDomainAliasForm\" class=' mail_form expandable'  data-function-string='MailDomainLibrary.domains[from].addAliasTarget(to)'>
            <div>
            <label>
                Domæne
                <select name='from'>
                    {$this->getDomainOptions(true)}
                </select>
            </label>
            <label>
                Peger på
                <select  name='to'>

                    {$this->getDomainOptions()}
                </select>
            </label>
            <div class='submit'>
            <input type='submit' value='Opret Alias'/>
            </div>
            </div>
        </form>
        <h3>Adresser</h3>
        {$this->getAddressList()}
        <form id='UserSettingsEditMailAddAddressForm' class='mail_form expandable' data-function-string='MailDomainLibrary.domains[domain].aliasLibrary.createAlias(local_part)'>
            <div>
            <label>
                Navn (tom for catchall addresse)
                <input type='text' name='local_part'>
            </label>
            <span class='at'>@</span>
            <label>
                Domæne
                <select name='domain'>
                    {$this->getDomainOptions()}
                </select>
            </label>
            $userForm
            <label class='long_input'>
                Vidersend til (komma separeret liste)
                <input type='text' name='targets' />
            </label>
            <input type='checkbox' class='pretty_checkbox' id='UserSettingsEditMailAddAddressAddMailboxCheckbox' name='add_mailbox' value='1'/>
            <label for='UserSettingsEditMailAddAddressAddMailboxCheckbox' class='long_input'>
                Opret mailbox
            </label>
            <div>
            <label>
            Navn
            <input type='text' name='mailbox_owner_name' />
            </label>
            <label>
            Mailbox kodeord
            <input type='password' name='mailbox_password' />
            </label>
            <label>
            Bekræft kodeord
            <input type='password' name='mailbox_password_2' />
            </label>

            </div>
            <div class='submit'>
                <input type='submit' value='Opret adresse' />


            </div>
            </div>

        </form>
        ";

        //TODO: style empty lists

        return $out;

    }

    private function getDomainList()
    {
        $result = "";
        $deleteElement = $this->backendContainer->getUserLibraryInstance()->getUserLoggedIn()->getUserPrivileges()->hasSitePrivileges() ? "<div class='delete'></div>" : "";
        foreach ($this->mailDomainLibrary->listDomains() as $domain) {

            $result .= "
            <li data-last-modified='{$domain->lastModified()}' data-description='{$domain->getDescription()}' data-active='{$domain->isActive()}' data-domain-name='{$domain->getDomainName()}'>
                {$domain->getDomainName()}$deleteElement
            </li>
            ";
        }

        $hiddenEmpty = $result == "" ? "" : "hidden";
        $result .= "<li class='empty_list' $hiddenEmpty>Der er ingen domæner</li>";

        return $result;

    }

    private function getMailAliasList()
    {

        $result = "";

        $deleteElement = $this->backendContainer->getUserLibraryInstance()->getUserLoggedIn()->getUserPrivileges()->hasSitePrivileges() ? "<div class='delete'></div>" : "";

        foreach ($this->mailDomainLibrary->listDomains() as $domain) {
            if (!$domain->isAliasDomain()) {
                continue;
            }
            $target = $domain->getAliasTarget();

            $result .= "
        <li data-from-domain='{$domain->getDomainName()}' data-to-domain='{$target->getDomainName()}'>
                <div>
                {$domain->getDomainName()}
                </div>
                <div class='arrow'>

                </div>
                <div>
                {$target->getDomainName()}
                </div>
                $deleteElement
            </li>
            ";
        }

        $hiddenEmpty = $result == "" ? "" : "hidden";
        $result .= "<li class='empty_list' $hiddenEmpty>Der er ingen domæne alias</li>";

        return $result;

    }

    private function getAddressList()
    {

        $result = "";

        foreach ($this->mailDomainLibrary->listDomains() as $domain) {
            $addressLibrary = $domain->getAddressLibrary();
            $addresses = $addressLibrary->listAddresses();
            if (!count($addresses)) {
                continue;
            }
            $result .= "<ul id='UserSettingsEditMailAddressList{$domain->getDomainName()}' class='floating_list has_deletable' data-domain='{$domain->getDomainName()}'>";
            foreach ($addresses as $address) {
                $result .= $this->getAddressElement($address, $domain);

            }

            if ($addressLibrary->hasCatchallAddress()) {
                $result .= $this->getAddressElement($addressLibrary->getCatchallAddress(), $domain, " class='catchall'");
            }

            $result .= "</ul>";
        }

        $hideNoResult = $result == ""?"":"hidden";

        $result .= "<ul class='floating_list' $hideNoResult><li class='empty_list'>Der er ingen addresser</li> </ul>";

        return $result;
    }

    private function getAddressElement(Address $address, Domain $domain, $attributes = '')
    {
        $attributes .= ' data-local-part="' . $address->getLocalPart() . '"';
        $attributes .= ' data-targets="' . implode(" ", $address->getTargets()) . '"';
        $attributes .= ' data-last-modified="' . $address->lastModified() . '"';
        $attributes .= ' data-owners="' . implode(" ", $address->listOwners()) . '"';
        $attributes .= ' data-active="' . ($address->isActive() ? "true" : "false") . '"';
        $attributes .= ' data-active="' . ($address->hasMailbox() ? "true" : "false") . '"';
        if ($address->hasMailbox()) {
            $attributes .= ' data-mailbox-name="' . $address->getMailbox()->getName() . '"';
            $attributes .= ' data-mailbox-last-modified="' . $address->getMailbox()->lastModified() . '"';
        }
        $deleteElement = $this->currentUser != null && ($address->isOwner($this->currentUser) || $this->currentUser->getUserPrivileges()->hasSitePrivileges()) ?
            "<div class='delete'></div>" : "";

        $lp = $address->getLocalPart() == ""?"<span class='asterisk'>*</span>":$address->getLocalPart();
        return "<li $attributes>
                    {$lp}@{$domain->getDomainName()}$deleteElement
                            </li>";
    }

    private function getDomainOptions($from = false)
    {
        $result = "";
        foreach($this->mailDomainLibrary->listDomains() as $domain){
            if($from && $domain->isAliasDomain()){
                continue;
            }
            $result .= "
            <option value='{$domain->getDomainName()}'>
                {$domain->getDomainName()}
            </option>
            ";
        }

        return $result;
    }

    private function getPageUserList()
    {


        $result = "";
        $i = 0;

        foreach($this->backendContainer->getUserLibraryInstance()->listUsers() as $user){
            $privileges = $user->getUserPrivileges();
            if($privileges->hasSitePrivileges()){
               continue;
            }
            $result .= "
                       <li>
                    <input type='checkbox' id='UserSettingsEditMailAddAddressFormAddUserCheck$i' data-function-string='.addOwner(_this)' name='user_$i' value='{$user->getUsername()}'/>
                    <label for='UserSettingsEditMailAddAddressFormAddUserCheck$i'>
                        {$user->getUsername()}
                    </label>
                </li>";
            $i++;
        }

        return $result;
    }


}