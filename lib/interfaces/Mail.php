<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 02/08/12
 * Time: 14:10
 */
interface Mail
{

    const MAIL_TYPE_HTML = 'html';
    const MAIL_TYPE_PLAIN = 'plain';

    /**
     * @abstract
     * @param string | User $sender
     * @param string $name
     * @return bool FALSE if not instance of User or not valid mail else TRUE
     */
    public function setSender($sender, $name = "");

    /**
     * @abstract
     * @param string | User $receiver
     * @param string $name
     * @return bool FALSE if not instance of User or not valid mail else TRUE
     */
    public function addReceiver($receiver, $name = "");

    /**
     * @abstract
     * @param string | User $cc
     * @return bool FALSE if not instance of User or not valid mail else TRUE
     */
    public function addCC($cc);

    /**
     * @abstract
     * @param string | User $bcc
     * @return bool FALSE if not instance of User or not valid mail else TRUE
     */
    public function addBCC($bcc);

    /**
     * @abstract
     * @param string $subject
     * @return void
     */
    public function setSubject($subject);

    /**
     * @abstract
     * @param string $message
     * @return void
     */
    public function setMessage($message);

    /**
     * @abstract
     * @return bool FALSE on failure else TRUE
     */
    public function sendMail();

    /**
     * @abstract
     * Will set the mail type.
     * Possible mail types are specified in the Mail interface as MAIL_TYPE_* constants.
     * @param string $type
     * @return void
     */
    public function setMailType($type);

}
