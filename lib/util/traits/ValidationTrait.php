<?php
namespace ChristianBudde\cbweb\util\traits;
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 11/09/12
 * Time: 21:13
 */
trait ValidationTrait
{
    protected function validMail($mail){
        return @preg_match('/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/i',$mail) == 1;
    }
}
