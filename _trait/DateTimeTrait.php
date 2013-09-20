<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 10/27/12
 * Time: 11:31 AM
 * To change this template use File | Settings | File Templates.
 */
trait DateTimeTrait
{

    protected function monthNumberToName($number){
        $monthArray = array('januar','februar','marts','april','maj','juni','juli','august','september','november','december');
        return $monthArray[($number-1)%12];
    }

    protected function dayNumberToName($number){

        $monthArray = array('mandag','tirsdag','onsdag','torsdag','fredag','lørdag','søndag');
        return $monthArray[($number-1)%7];
    }


    protected function addLeadingZero($number){
        return $number < 10 ? "0$number":"$number";
    }

}
