<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/17/12
 * Time: 4:22 PM
 * To change this template use File | Settings | File Templates.
 */
interface Observable
{

    public function attachObserver(Observer $observer);

    public function detachObserver(Observer $observer);


}
