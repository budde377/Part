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

        $ownerCheckList = $this->getPageUserList();
        $ownerCheckListHidden = $ownerCheckList == ""?"hidden":"";

        $out = "
        <h3>Domæner</h3>
        <ul class='floating_list has_deletable' id='UserSettingsEditMailDomainList'>
            {$this->getDomainList()}
        </ul>
        <div class='mail_form expandable'>
        <form id='UserSettingsEditMailAddDomainForm'  data-function-string='MailDomainLibrary.createDomain(domain_name,super_password)'>
            <div hidden>
                <input type='text' />
                <input type='password'/>
            </div>
            <label>
                Domæne
                <input type='text' name='domain_name' data-validator-method='pattern' data-pattern='^[a-z0-9-_\\.]+\\.[a-z]{2,}$' data-error-message='Ugyldig domæne'/>
            </label>
            <label>
                Super-kodeord
                <input type='password' name='super_password' data-validator-method='non-empty' data-error-message='Ugyldig kodeord'/>
            </label>

            <div class='submit'>
            <input type='submit' value='Opret Domæne'/>
            </div>
        </form>
        </div>
        <h3>Domæne alias</h3>
        <ul class='floating_list points_to has_deletable' id='UserSettingsEditMailDomainAliasList'>
            {$this->getMailAliasList()}
        </ul>
                <div class='mail_form expandable'>

        <form id=\"UserSettingsEditMailAddDomainAliasForm\"  data-function-string='MailDomainLibrary.domains[from].addAliasTarget(to)'>
            <label>
                Domæne
                <select name='from' data-validator-method='non-empty'>
                    <option value=''>--Domæne--</option>
                    {$this->getDomainOptions(true)}
                </select>
            </label>
            <label>
                Peger på
                <select  name='to' data-validator-method='non-empty'>
                    <option value=''>--Domæne--</option>
                    {$this->getDomainOptions()}
                </select>
            </label>
            <div class='submit'>
            <input type='submit' value='Opret Alias'/>
            </div>
        </form>
        </div>
        <h3>Adresser</h3>
        {$this->getAddressList()}
        <div class='mail_form expandable'>
        <form id='UserSettingsEditMailAddAddressForm'  data-function-string='MailDomainLibrary.domains[domain].aliasLibrary.createAlias(local_part)'>
            <div hidden>
                <input type='text' />
                <input type='password'/>
            </div>            <label>
                Navn (tom for catchall addresse)
                <input type='text' name='local_part' data-validator-method='pattern' data-pattern='^[a-z0-9\\._-]*$' data-error-message='Ugyldig addresse'>
            </label>
            <span class='at'>@</span>
            <label>
                Domæne
                <select name='domain' data-validator-method='non-empty'>
                    <option value=''>--Domæne--</option>
                    {$this->getDomainOptions()}
                </select>
            </label>
            <label>
                Vælg brugere
            </label>
            <ul class='owner_check_list' id='UserSettingsEditMailAddAddressUserCheckList' $ownerCheckListHidden>
                $ownerCheckList
            </ul>
            <label class='long_input'>
                Vidersend til (mellemrums separeret liste)
                <input type='text' name='targets' data-error-message='Skal være liste af gyldige email addresser'/>
            </label>
            <input type='checkbox' class='pretty_checkbox' id='UserSettingsEditMailAddAddressAddMailboxCheckbox' name='add_mailbox' value='1'/>
            <label for='UserSettingsEditMailAddAddressAddMailboxCheckbox' class='long_input'>
                Opret mailbox
            </label>
            <div>
            <label>
            Navn
            <input type='text' name='mailbox_owner_name' data-error-message='Der skal angives et navn' />
            </label>
            <label>
            Mailbox kodeord
            <input type='password' name='mailbox_password'  data-error-message='Kodeordet må ikke være tomt'/>
            </label>
            <label>
            Bekræft kodeord
            <input type='password' name='mailbox_password_2' data-error-message='Kodeordet skal gengives korrekt'/>
            </label>

            </div>
            <div class='submit'>
                <input type='submit' value='Opret adresse' />


            </div>

        </form>
        </div>
        ";


        return $out;

    }

    private function getDomainList()
    {
        $result = "";
        $deleteElement = $this->backendContainer->getUserLibraryInstance()->getUserLoggedIn()->getUserPrivileges()->hasSitePrivileges() ? "<div class='delete'></div>" : "";
        foreach ($this->mailDomainLibrary->listDomains() as $domain) {

            $result .= "
            <li
            data-last-modified='{$domain->lastModified()}'
            data-description='{$domain->getDescription()}'
            data-active='" . ($domain->isActive() ? 'true' : 'false') . "'
            data-domain-name='{$domain->getDomainName()}'
            data-alias-target='" . ($domain->isAliasDomain() ? $domain->getAliasTarget()->getDomainName() : "") . "'>
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
        $addressesFound = false;

        foreach ($this->mailDomainLibrary->listDomains() as $domain) {
            $addressLibrary = $domain->getAddressLibrary();
            $addresses = $addressLibrary->listAddresses();

            $hidden = count($addresses) ? "" : "hidden";
            $result .= "<ul id='UserSettingsEditMailAddressList{$domain->getDomainName()}' class='floating_list has_deletable address_list' data-domain='{$domain->getDomainName()}' $hidden>";
            foreach ($addresses as $address) {
                $result .= $this->getAddressElement($address, $domain);
                $addressesFound = true;
            }

            if ($addressLibrary->hasCatchallAddress()) {
                $result .= $this->getAddressElement($addressLibrary->getCatchallAddress(), $domain, " class='catchall'");
            }

            $result .= "</ul>";
        }

        $hideNoResult = !$addressesFound ? "" : "hidden";

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
        $attributes .= ' data-has-mailbox="' . ($address->hasMailbox() ? "true" : "false") . '"';
        if ($address->hasMailbox()) {
            $attributes .= ' data-mailbox-name="' . $address->getMailbox()->getName() . '"';
            $attributes .= ' data-mailbox-last-modified="' . $address->getMailbox()->lastModified() . '"';
        }
        $deleteElement = $this->currentUser != null && ($address->isOwner($this->currentUser) || $this->currentUser->getUserPrivileges()->hasSitePrivileges()) ?
            "<div class='delete'></div>" : "";

        $lp = $address->getLocalPart() == "" ? "<span class='asterisk'>*</span>" : $address->getLocalPart();
        return "<li $attributes>
                    {$lp}@{$domain->getDomainName()}$deleteElement
                            </li>";
    }

    private function getDomainOptions($from = false)
    {
        $result = "";
        foreach ($this->mailDomainLibrary->listDomains() as $domain) {
            if ($from && $domain->isAliasDomain()) {
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

        foreach ($this->backendContainer->getUserLibraryInstance()->listUsers() as $user) {
            $privileges = $user->getUserPrivileges();
            if ($privileges->hasSitePrivileges()) {
                continue;
            }
            $result .= "
                       <li  data-user-name='{$user->getUsername()}' >
                    <input type='checkbox' id='UserSettingsEditMailAddAddressFormAddUserCheck{$user->getUsername()}' data-function-string='.addOwner(_this)' name='user_{$user->getUsername()}' value='{$user->getUsername()}'/>
                    <label for='UserSettingsEditMailAddAddressFormAddUserCheck{$user->getUsername()}'>
                        {$user->getUsername()}
                    </label>
                </li>";
            $i++;
        }

        return $result;
    }


}