<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/3/14
 * Time: 2:13 PM
 */

interface PostfixMailbox extends PostfixAddress{

    /**
     * Sets the owners name of the mailbox
     * @param string $name
     * @return void
     */
    public function setName($name);

    /**
     * @return string The name of the owner
     */
    public function getName();

    /**
     * Sets the password of the mailbox
     * @param string $password
     * @return void
     */
    public function setPassword($password);

} 