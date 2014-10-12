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
        <li>christianbud.de</li>
        <li>christian-budde.dk</li>
        <li>christianbudde.dk</li>
        </ul>
        <form id='UserSettingsEditMailAddDomainForm' class=' mail_form'>
            <input type='checkbox' class='enable_state_checkbox' id='UserSettingsEditMailAddDomainFormCheckbox'/>
            <label for='UserSettingsEditMailAddDomainFormCheckbox' class='no_select'></label>
            <div class='mail_form_container'>
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
        <ul class='floating_list points_to'>
            <li>
                <div>
                    christianbud.de
                </div>
                <div class='arrow'>

                </div>
                <div>
                    christianbudde.dk
                </div>
            </li>
        </ul>
        <form id=\"UserSettingsEditMailAddDomainAliasForm\" class=' mail_form'>
            <input type='checkbox' class='enable_state_checkbox' id='UserSettingsEditMailAddDomainAliasFormCheckbox'/>
            <label for='UserSettingsEditMailAddDomainAliasFormCheckbox' class='no_select'></label>
            <div class='mail_form_container'>
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
        <ul class='floating_list'>
            <li>test@christian-budde.dk</li>
            <li>test1@christian-budde.dk</li>
            <li>test2@christian-budde.dk</li>
            <li>test3@christian-budde.dk</li>
            <li>test4@christian-budde.dk</li>
            <li>test5@christian-budde.dk</li>
            <li>test6@christian-budde.dk</li>
            <li>test7@christian-budde.dk</li>
        </ul>
        <ul class='floating_list'>
            <li>bob@christianbudde.dk</li>
            <li>bob1@christianbudde.dk</li>
            <li>bob2@christianbudde.dk</li>

        </ul>
        <form id='UserSettingsEditMailAddAddressForm' class='mail_form'>

            <input type='checkbox' class='enable_state_checkbox' id='UserSettingsEditMailAddAddressFormCheckbox'/>
            <label for='UserSettingsEditMailAddAddressFormCheckbox' class='no_select'></label>
            <div class='mail_form_container'>
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
            <input type='checkbox' id='UserSettingsEditMailAddAddressAddMailboxCheckbox'/>
            <label for='UserSettingsEditMailAddAddressAddMailboxCheckbox' class='long_input'>
                Opret mailbox:
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