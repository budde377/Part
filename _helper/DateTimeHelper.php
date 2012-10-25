<?php
//TODO Convert this to trait

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 26/07/12
 * Time: 17:15
 */
class DateTimeHelper
{

    public static function monthNumberToName($number){
        $monthArray = array('januar','februar','marts','april','maj','juni','juli','august','september','november','december');
        return $monthArray[($number-1)%12];
    }

    public static function dayNumberToName($number){

        $monthArray = array('mandag','tirsdag','onsdag','torsdag','fredag','lørdag','søndag');
        return $monthArray[($number-1)%7];
    }

    

}
