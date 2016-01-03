<?php
namespace ChristianBudde\Part\exception;
use Exception;

/**
 * User: budde
 * Date: 5/28/12
 * Time: 3:03 PM
 */
class TaskHasRunException extends Exception
{

    public function __construct()
    {

        parent::__construct('The script has run');
    }


}
