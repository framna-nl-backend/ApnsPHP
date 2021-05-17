<?php

/**
 * This file contains the MessageAddRecipientTest class.
 *
 * @package ApnsPHP
 * @author  Martijn van Berkum <m.vanberkum@m2mobi.com>
 */

namespace ApnsPHP\Tests;

/**
 * This class contains tests for the addRecipient function
 *
 * @covers \ApnsPHP\Message
 */
class MessageAddRecipientTest extends MessageTest
{

    /**
     * Test that addRecipient throws exception on invalid token
     * @covers \ApnsPHP\Message::addRecipient
     */
    public function testAddRecipientThrowsExceptionOnInvalidToken(): void
    {
        $token = '1e82db91c7ceddd72bf33d74ae052ac9c84a065b35148ac401388843106a7485L';

        $this->expectException('ApnsPHP\Exception');
        $this->expectExceptionMessage('Invalid device token \'' . $token . '\'');

        $this->class->addRecipient($token);
    }

    /**
     * Test addRecipient successfully adds a token
     * @covers \ApnsPHP\Message::addRecipient
     */
    public function testAddRecipientSucceeds(): void
    {
        $token = '1e82db91c7ceddd72bf33d74ae052ac9c84a065b35148ac401388843106a7485';

        $this->class->addRecipient($token);

        $this->assertSame($token, $this->class->getRecipient(0));
    }
}
