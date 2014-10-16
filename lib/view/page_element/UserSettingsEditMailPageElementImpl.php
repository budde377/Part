<?php
namespace ChristianBudde\cbweb\view\page_element;


/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/13/14
 * Time: 9:29 AM
 */

class UserSettingsEditMailPageElementImpl extends PageElementImpl
{
    public function generateContent()
    {
        $out = "
        <h3>Domæner</h3>
        <ul class='floating_list'>
        <li>christianbud.de<div class='delete'></div></li>
        <li>christian-budde.dk<div class='delete'></div></li>
        <li>christianbudde.dk<div class='delete'></div></li>
        </ul>
        <form id='UserSettingsEditMailAddDomainForm' class=' mail_form expandable'>
            <div>
            <label>
                Domæne
                <input type='text' />
            </label>
            <label>
                Super-kodeord
                <input type='password' />
            </label>
            <div class='submit'>
            <input type='submit' value='Opret Alias'/>
            </div>
            </div>
        </form>
        <h3>Domæne alias</h3>
        <ul class='floating_list points_to has_deletable'>
            <li>
                <div>
                    christianbud.de
                </div>
                <div class='arrow'>

                </div>
                <div>
                    christianbudde.dk
                </div>
            <div class='delete'></div></li>
        </ul>
        <form id=\"UserSettingsEditMailAddDomainAliasForm\" class=' mail_form expandable'>
            <div>
            <label>
                Domæne
                <select>
                    <option>christianbudde.dk</option>
                    <option>christian-budde.dk</option>
                </select>
            </label>
            <label>
                Peger på
                <select>
                    <option>christianbudde.dk</option>
                    <option>christian-budde.dk</option>
                    <option>christianbud.de</option>
                </select>
            </label>
            <div class='submit'>
            <input type='submit' value='Opret Alias'/>
            </div>
            </div>
        </form>
        <h3>Adresser</h3>
        <ul class='floating_list has_deletable'>
            <li>test@christian-budde.dk<div class='delete'></div></li>
            <li>test1@christian-budde.dk<div class='delete'></div></li>
            <li>test2@christian-budde.dk<div class='delete'></div></li>
            <li>test3@christian-budde.dk<div class='delete'></div></li>
            <li>test4@christian-budde.dk<div class='delete'></div></li>
            <li>test5@christian-budde.dk<div class='delete'></div></li>
            <li>test6@christian-budde.dk<div class='delete'></div></li>
            <li>test7@christian-budde.dk<div class='delete'></div></li>
        </ul>
        <ul class='floating_list has_deletable'>
            <li>bob@christianbudde.dk<div class='delete'></div></li>
            <li>bob1@christianbudde.dk<div class='delete'></div></li>
            <li>bob2@christianbudde.dk<div class='delete'></div></li>
        </ul>
        <form id='UserSettingsEditMailAddAddressForm' class='mail_form expandable'>
            <div>
            <label>
                Navn
                <input type='text'>
            </label>
            <span class='at'>@</span>
            <label>
                Domæne
                <select>
                    <option>christianbudde.dk</option>
                    <option>christian-budde.dk</option>
                    <option>christianbud.de</option>
                </select>
            </label>
            <label>
                Vælg brugere
            </label>
            <ul class='owner_check_list'>
                <li>
                    <input type='checkbox' id='someUniqueId'/>
                    <label for='someUniqueId'>
                        budde377
                    </label>
                </li>
                <li>
                    <input type='checkbox' id='someUniqueId2'/>
                    <label for='someUniqueId2'>
                        bent
                    </label>
                </li>
            </ul>
            <label class='long_input'>
                Vidersend til (komma separeret liste)
                <input type='text' />
            </label>
            <input type='checkbox' class='pretty_checkbox' id='UserSettingsEditMailAddAddressAddMailboxCheckbox'/>
            <label for='UserSettingsEditMailAddAddressAddMailboxCheckbox' class='long_input'>
                Opret mailbox
            </label>
            <div>
            <label>
            Mailbox kodeord
            <input type='password'>
            </label>
            <label>
            Bekræft kodeord
            <input type='password'>
            </label>

            </div>
            <div class='submit'>
                <input type='submit' value='Opret adresse' />


            </div>
            </div>

        </form>
        ";


        return $out;

    }


}