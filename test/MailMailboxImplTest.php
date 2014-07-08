<?php
/**
 * Created by PhpStorm.
 * User: budde
 * Date: 7/8/14
 * Time: 6:08 PM
 */

class MailMailboxImplTest extends CustomDatabaseTestCase{


    private $mailbox;

    public function setUp(){
        $this->mailbox = new MailMailboxImpl();
    }


} 