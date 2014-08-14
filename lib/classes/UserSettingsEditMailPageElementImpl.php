<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/13/14
 * Time: 9:29 AM
 */

class UserSettingsEditMailPageElementImpl extends PageElementImpl{
    public function generateContent()
    {
        $out = "
        <h3>Domæner</h3>
        <ul class='floating_list'>
        <li>christianbud.de</li>
        <li>christian-budde.dk</li>
        <li>christianbudde.dk</li>
            <li class='add'></li>
        </ul>
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
            <li class='add'></li>
        </ul>

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
            <li class='add'></li>
        </ul>
        <ul class='floating_list'>
            <li>bob@christianbudde.dk</li>
            <li>bob1@christianbudde.dk</li>
            <li>bob2@christianbudde.dk</li>
            <li>bob3@christianbudde.dk</li>
            <li>bob4@christianbudde.dk</li>
            <li>bob5@christianbudde.dk</li>
            <li>bob6@christianbudde.dk</li>
            <li>bob7@christianbudde.dk</li>
            <li class='add'></li>
        </ul>

        ";


        return $out;

    }


}