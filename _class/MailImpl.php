<?php
require_once dirname(__FILE__) . '/../_interface/Mail.php';
require_once dirname(__FILE__) . '/RealSendMailStrategyImpl.php';
require_once dirname(__FILE__) .'/../_trait/ValidationTrait.php';
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 02/08/12
 * Time: 14:52
 */
class MailImpl implements Mail
{
    use ValidationTrait;

    private $sendMailStrategy;

    private $sender;

    private $receivers = '';
    private $CCs = '';
    private $BCCs = '';
    private $subject;
    private $message;

    private $mailTypeHeaders = array();

    public function __construct(SendMailStrategy $sendMailStrategy = null)
    {
        if ($sendMailStrategy !== null) {
            $this->sendMailStrategy = $sendMailStrategy;
        } else {
            $this->sendMailStrategy = new RealSendMailStrategyImpl();
        }
    }
    /**
     * @return bool FALSE on failure else TRUE
     */
    public function sendMail()
    {
        $receivers = ($len = strlen($this->receivers)) >0?substr($this->receivers,0,$len-2):'';
        $CCs = ($len = strlen($this->CCs)) >0?substr($this->CCs,0,$len-2):'';
        $BCCs = ($len = strlen($this->BCCs)) >0?substr($this->BCCs,0,$len-2):'';
        return $this->sendMailStrategy->sendMail($this->sender,$receivers, $this->subject, $this->message, $CCs, $BCCs,$this->mailTypeHeaders);
    }

    /**
     * Will set the mail type.
     * Possible mail types are specified in the Mail interface as MAIL_TYPE_* constants.
     * @param string $type
     * @return void
     */
    public function setMailType($type)
    {
        switch($type){
            case Mail::MAIL_TYPE_HTML:
                $this->mailTypeHeaders = array();
                $this->mailTypeHeaders[] = 'MIME-Version: 1.0';
                $this->mailTypeHeaders[] = 'Content-type: text/html; charset=UTF-8';
                break;
            case Mail::MAIL_TYPE_PLAIN:
                $this->mailTypeHeaders = array();
                break;
        }
    }

    /**
     * @param string | User $sender
     * @return bool FALSE if not instance of User or not valid mail else TRUE
     */
    public function setSender($sender)
    {
        if (($s = $this->checkMail($sender)) !== false) {
            $this->sender = $s;
            return true;
        }
        return false;
    }

    /**
     * @param string | User $receiver
     * @return bool FALSE if not instance of User or not valid mail else TRUE
     */
    public function addReceiver($receiver)
    {
        if (($r = $this->checkMail($receiver))) {
            $this->receivers .= $r.', ';
            return true;
        }
        return false;

    }

    /**
     * @param string | User $cc
     * @return bool FALSE if not instance of User or not valid mail else TRUE
     */
    public function addCC($cc)
    {
        if (($r = $this->checkMail($cc))) {
            $this->CCs .= $r.', ';
            return true;
        }
        return false;
    }

    /**
     * @param string | User $bcc
     * @return bool FALSE if not instance of User or not valid mail else TRUE
     */
    public function addBCC($bcc)
    {
        if (($r = $this->checkMail($bcc))) {
            $this->BCCs .= $r.', ';
            return true;
        }
        return false;
    }

    /**
     * @param string $subject
     * @return void
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @param string $message
     * @return void
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @param User | string $mail
     * @return bool
     */
    private function checkMail($mail)
    {
        if ($mail instanceof User) {
            return $this->checkMail($mail->getMail());
        } else if ($this->validMail($mail)) {
            return $mail;
        }
        return false;
    }
}
