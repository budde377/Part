<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 02/08/12
 * Time: 14:57
 */
class RealSendMailStrategyImpl implements SendMailStrategy
{

    /**
     * @param string $from
     * @param string $to
     * @param string $cc
     * @param string $bcc
     * @param string $subject
     * @param string $message
     * @param array $additionalHeaders
     * @return bool
     */
    public function sendMail($from, $to, $subject, $message, $cc, $bcc, $additionalHeaders = array())
    {
        $headers = '';
        foreach ($additionalHeaders as $header) {
            $headers .= $header . "\r\n";
        }
        $headers .= "From: $from" . "\r\n";
        $headers .= "Cc: $cc" . "\r\n";
        $headers .= "Bcc: $bcc" . "\r\n";

        return @mail($to,$subject,$message,$headers);
    }
}
