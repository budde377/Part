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
        <ul class='colorList'>
        <li>christianbud.de</li>
        <li>christian-budde.dk</li>
        <li>christianbudde.dk</li>
        </ul>

        <h3>Adresser</h3>
        <ul class='colorList'>
            <li>

            test@christian-budde.dk

            </li>

        </ul>

        ";


        return $out;

    }


}