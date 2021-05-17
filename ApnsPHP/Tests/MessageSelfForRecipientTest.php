<?php

/**
 * This file contains the MessageSelfForRecipientTest class.
 *
 * @package ApnsPHP
 * @author  Martijn van Berkum <m.vanberkum@m2mobi.com>
 */

namespace ApnsPHP\Tests;

use ApnsPHP\Message;

/**
 * This class contains tests for the selfForRecipient function
 *
 * @covers \ApnsPHP\Message
 */
class MessageSelfForRecipientTest extends MessageTest
{

    /**
     * Test that selfForRecipient throws exception on invalid index
     *
     * @covers \ApnsPHP\Message::selfForRecipient
     */
    public function testselfForRecipientThrowsExceptionOnInvalidIndex(): void
    {
        $this->expectException('ApnsPHP\Exception');
        $this->expectExceptionMessage('No recipient at index \'1\'');

        $this->class->selfForRecipient(1);
    }

    /**
     * Test that selfForRecipient returns a Message
     *
     * @covers \ApnsPHP\Message::selfForRecipient
     */
    public function testselfForRecipientGetsMessage(): void
    {
        $token   = '1e82db91c7ceddd72bf33d74ae052ac9c84a065b35148ac401388843106a7485';
        $message = new Message($token);

        $this->class->addRecipient($token);

        $result = $this->class->selfForRecipient(0);

        $this->assertEquals($message, $result);
    }
}
