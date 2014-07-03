<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/3/14
 * Time: 2:13 PM
 */

interface PostfixMailbox extends PostfixAddress{

    public function setName($name);

    public function getName();

    public function getMailDir();

    public function getDomain();

} 