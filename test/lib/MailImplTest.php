<?php
/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 02/08/12
 * Time: 14:54
 */
namespace ChristianBudde\cbweb\test;

use ChristianBudde\cbweb\util\mail\MailImpl;
use ChristianBudde\cbweb\util\mail\Mail;

class MailImplTest extends PHPUnit_Framework_TestCase
{
    use \ChristianBudde\cbweb\util\traits\ValidationTrait;
    /** @var $strategy StubSendMailStrategyImpl */
    private $strategy;
    /** @var $mail \ChristianBudde\cbweb\util\mail\MailImpl */
    private $mail;

    private $validMail = "test@test.dk";

    public function setUp()
    {
        $this->strategy = new StubSendMailStrategyImpl();
        $this->mail = new MailImpl($this->strategy);
    }


    public function testValidMailWillReturnTrueOnValidMail()
    {
        $this->assertTrue($this->validMail($this->validMail), 'Did not return true on valid mail');
    }

    public function testWillReturnFalseOnInvalidMail()
    {
        $invalidMail = '';
        $this->assertFalse($this->validMail($invalidMail), 'Did not return false on invalid mail');
        $invalidMail = '@';
        $this->assertFalse($this->validMail($invalidMail), 'Did not return false on invalid mail');
        $invalidMail = 'test';
        $this->assertFalse($this->validMail($invalidMail), 'Did not return false on invalid mail');
        $invalidMail = 'test@';
        $this->assertFalse($this->validMail($invalidMail), 'Did not return false on invalid mail');
        $invalidMail = 'test@test';
        $this->assertFalse($this->validMail($invalidMail), 'Did not return false on invalid mail');
        $invalidMail = 'test@test.d';
        $this->assertFalse($this->validMail($invalidMail), 'Did not return false on invalid mail');
        $invalidMail = 'test@.dk';
        $this->assertFalse($this->validMail($invalidMail), 'Did not return false on invalid mail');
        $invalidMail = 'test@test.dkkkk';
        $this->assertFalse($this->validMail($invalidMail), 'Did not return false on invalid mail');
    }

    public function testSendMailWillCallMailStrategy()
    {
        $this->mail->sendMail();
        $this->assertTrue($this->strategy->isCalled());
    }

    public function testSetFromWithValidMailWillReturnTrue()
    {

        $ret = $this->mail->setSender($this->validMail);
        $this->assertTrue($ret, 'Did not return true');

        $user = new StubUserImpl();
        $user->setMail($this->validMail);
        $user->setUsername('someUsername');

        $ret = $this->mail->setSender($user);
        $this->assertTrue($ret, 'Did not return true');
    }

    public function testSetFromWithInvalidMailWillReturnFalse()
    {
        $invalidMail = 'test';
        $ret = $this->mail->setSender($invalidMail);
        $this->assertFalse($ret, 'Did not return false');

        $user = new StubUserImpl();
        $user->setMail($invalidMail);
        $user->setUsername('someUsername');

        $ret = $this->mail->setSender($user);
        $this->assertFalse($ret, 'Did not return false');

        $ret = $this->mail->setSender($this);
        $this->assertFalse($ret, 'Did not return false');
    }


    public function testSetSenderNameWillSetName()
    {
        $name = "Lars Larsen";
        $this->strategy->setReturnValue(true);
        $this->assertTrue($this->mail->setSender($this->validMail, $name));
        $this->assertTrue($this->mail->sendMail());
        $this->assertEquals("$name <{$this->validMail}>", $this->strategy->getFrom());
    }

    public function testAddReceiverWillReturnTrueWithValidMail()
    {

        $ret = $this->mail->addReceiver($this->validMail);
        $this->assertTrue($ret, 'Did not return true');

        $user = new StubUserImpl();
        $user->setMail($this->validMail);
        $user->setUsername('someUsername');

        $ret = $this->mail->addReceiver($user);
        $this->assertTrue($ret, 'Did not return true');
    }

    public function testAddReceiverWillReturnFalseWithInvalidMail()
    {
        $invalidMail = 'test';
        $ret = $this->mail->addReceiver($invalidMail);
        $this->assertFalse($ret, 'Did not return false');

        $user = new StubUserImpl();
        $user->setMail($invalidMail);
        $user->setUsername('someUsername');

        $ret = $this->mail->addReceiver($user);
        $this->assertFalse($ret, 'Did not return false');

        $ret = $this->mail->addReceiver($this);
        $this->assertFalse($ret, 'Did not return false');
    }

    public function testAddReceiverWillAddName()
    {
        $name = "Lars Larsen";
        $this->strategy->setReturnValue(true);
        $this->assertTrue($this->mail->addReceiver($this->validMail, $name));
        $this->assertTrue($this->mail->sendMail());
        $this->assertEquals("$name <{$this->validMail}>", $this->strategy->getTo());
    }

    public function testAddCCrWillReturnTrueWithValidMail()
    {

        $ret = $this->mail->addCC($this->validMail);
        $this->assertTrue($ret, 'Did not return true');

        $user = new StubUserImpl();
        $user->setMail($this->validMail);
        $user->setUsername('someUsername');

        $ret = $this->mail->addCC($user);
        $this->assertTrue($ret, 'Did not return true');
    }

    public function testAddCCWillReturnFalseWithInvalidMail()
    {
        $invalidMail = 'test';
        $ret = $this->mail->addCC($invalidMail);
        $this->assertFalse($ret, 'Did not return false');

        $user = new StubUserImpl();
        $user->setMail($invalidMail);
        $user->setUsername('someUsername');

        $ret = $this->mail->addCC($user);
        $this->assertFalse($ret, 'Did not return false');

        $ret = $this->mail->addCC($this);
        $this->assertFalse($ret, 'Did not return false');
    }


    public function testAddBCCrWillReturnTrueWithValidMail()
    {

        $ret = $this->mail->addBCC($this->validMail);
        $this->assertTrue($ret, 'Did not return true');

        $user = new StubUserImpl();
        $user->setMail($this->validMail);
        $user->setUsername('someUsername');

        $ret = $this->mail->addBCC($user);
        $this->assertTrue($ret, 'Did not return true');
    }

    public function testAddBCCWillReturnFalseWithInvalidMail()
    {
        $invalidMail = 'test';
        $ret = $this->mail->addBCC($invalidMail);
        $this->assertFalse($ret, 'Did not return false');

        $user = new StubUserImpl();
        $user->setMail($invalidMail);
        $user->setUsername('someUsername');

        $ret = $this->mail->addBCC($user);
        $this->assertFalse($ret, 'Did not return false');

        $ret = $this->mail->addBCC($this);
        $this->assertFalse($ret, 'Did not return false');
    }

    public function testSetSubjectWillSetSubject()
    {
        $subject = 'some Subject';
        $this->mail->setSubject($subject);
        $this->mail->sendMail();
        $this->assertEquals($subject, $this->strategy->getSubject(), 'Subjects did not match');
    }

    public function testSetSenderWillSet()
    {
        $sender = 'test@test.dk';
        $this->mail->setSender($sender);
        $this->mail->sendMail();
        $this->assertEquals($sender, $this->strategy->getFrom(), 'Senders did not match');

    }

    public function testAddReceiverCCsBCCsWillAdd()
    {
        $mail1 = 'test1@test.dk';
        $mail2 = 'test2@test.dk';
        $mail3 = 'test3@test.dk';
        $mail4 = 'test4@test.dk';
        $mail5 = 'test5@test.dk';
        $mail6 = 'test6@test.dk';

        $this->mail->addReceiver($mail1);
        $this->mail->addReceiver($mail2);

        $this->mail->addCC($mail3);
        $this->mail->addCC($mail4);

        $this->mail->addBCC($mail5);
        $this->mail->addBCC($mail6);

        $this->mail->sendMail();

        $this->assertTrue(strpos($this->strategy->getTo(), $mail1) !== false, 'Did not contain mail');
        $this->assertTrue(strpos($this->strategy->getTo(), $mail2) !== false, 'Did not contain mail');

        $this->assertTrue(strpos($this->strategy->getCc(), $mail3) !== false, 'Did not contain mail');
        $this->assertTrue(strpos($this->strategy->getCc(), $mail4) !== false, 'Did not contain mail');

        $this->assertTrue(strpos($this->strategy->getBcc(), $mail5) !== false, 'Did not contain mail');
        $this->assertTrue(strpos($this->strategy->getBcc(), $mail6) !== false, 'Did not contain mail');

    }

    public function testSetMessageWillSet()
    {
        $message = 'some Message';
        $this->mail->setMessage($message);
        $this->mail->sendMail();
        $this->assertEquals($message, $this->strategy->getMessage());
    }

    public function testSetMailTypeToHTMLWillAddHeaders()
    {
        $this->mail->sendMail();
        $count1 = count($this->strategy->getAdditionalHeaders());

        $this->mail->setMailType(Mail::MAIL_TYPE_HTML);
        $this->mail->sendMail();
        $count2 = count($this->strategy->getAdditionalHeaders());

        $this->assertEquals(2, $count2 - $count1, 'Did not add headers');
    }

    public function testSendMailWillReturnTrueIfStrategyDoesElseFalse()
    {
        $this->strategy->setReturnValue(true);
        $this->assertTrue($this->mail->sendMail(), 'Did not return true');

        $this->strategy->setReturnValue(false);
        $this->assertFalse($this->mail->sendMail(), 'Did not return false');
    }

}
