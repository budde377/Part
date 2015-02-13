<?php
namespace ChristianBudde\Part\util\mail;
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 02/08/12
 * Time: 14:47
 */
interface SendMailStrategy
{
    /**
     * @abstract
     * @param string $from
     * @param string $to
     * @param string $cc
     * @param string $bcc
     * @param string $subject
     * @param string $message
     * @param array $additionalHeaders
     * @return bool
     */
    public function sendMail($from,$to, $subject, $message,$cc,$bcc,$additionalHeaders= array());
}
