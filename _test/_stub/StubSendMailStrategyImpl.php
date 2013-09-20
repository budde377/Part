<?php
require_once dirname(__FILE__) . '/../../_interface/SendMailStrategy.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 02/08/12
 * Time: 14:59
 */
class StubSendMailStrategyImpl implements SendMailStrategy
{

    private $called = false;
    private $additionalHeaders;
    private $from;
    private $to;
    private $bcc;
    private $message;
    private $cc;
    private $subject;
    private $returnValue;


    public function isCalled(){
        return $this->called;
    }

    public function getAdditionalHeaders()
    {
        return $this->additionalHeaders;
    }

    public function getBcc()
    {
        return $this->bcc;
    }

    public function getCc()
    {
        return $this->cc;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getTo()
    {
        return $this->to;
    }


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
    public function sendMail($from, $to,  $subject, $message,$cc, $bcc, $additionalHeaders = array())
    {
        $this->called = true;
        $this->from = $from;
        $this->to = $to;
        $this->cc = $cc;
        $this->bcc = $bcc;
        $this->subject = $subject;
        $this->message = $message;
        $this->additionalHeaders = $additionalHeaders;
        return $this->returnValue;
    }

    public function setReturnValue($returnValue)
    {
        $this->returnValue = $returnValue;
    }
}
