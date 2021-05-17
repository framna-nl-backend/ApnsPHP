<?php

/**
 * This file contains the MessageGetPayloadTest class.
 *
 * @package ApnsPHP
 * @author  Martijn van Berkum <m.vanberkum@m2mobi.com>
 */

namespace ApnsPHP\Tests;

/**
 * This class contains tests for the getPayload function
 *
 * @covers \ApnsPHP\Message
 */
class MessageGetPayloadTest extends MessageTest
{
    /**
     * Test that getPayload returns complete JSON encoded payload
     *
     * @covers \ApnsPHP\Message::getPayload
     */
    public function testGetPayloadReturnsCompletePayload(): void
    {
        $this->class->setTitle('Were no strangers to love');
        $this->class->setText('You know the rules, and so do I');
        $this->class->setBadge(1);
        $this->class->setSound('default');
        $this->class->setContentAvailable(true);
        $this->class->setMutableContent(true);
        $this->class->setCategory('something');
        $this->class->setThreadId('thisIsAThreadId');
        $this->class->setCustomProperty('propertie', 'propertie');
        $this->class->setCustomProperty('name', 'value');

        $payload = '{"aps":{"alert":{"title":"Were no strangers to love","body":"You know the rules, and so do I"},' .
                   '"badge":1,"sound":"default","content-available":1,"mutable-content":1,"category":"something",' .
                   '"thread-id":"thisIsAThreadId"},"propertie":"propertie","name":"value"}';

        $result = $this->class->getPayload();

        $this->assertEquals($payload, $result);
    }

    /**
     * Test that getPayload returns empty JSON encoded payload when nothing is set
     *
     * @covers \ApnsPHP\Message::getPayload
     */
    public function testGetPayloadReturnsEmptyPayload(): void
    {
        $result = $this->class->getPayload();

        $this->assertEquals('{"aps":{}}', $result);
    }

    /**
     * Test that getPayload throws an exception when the payload is too long and autoAdjustLongPayload is false.
     *
     * @covers \ApnsPHP\Message::getPayload
     */
    public function testGetPayloadThrowsExceptionOnTooLongPayload(): void
    {
        $this->class->setText($this->getLargeString());
        $this->class->setAutoAdjustLongPayload(false);

        $this->expectException('ApnsPHP\Exception');
        $this->expectExceptionMessage('JSON Payload is too long: 2090 bytes. Maximum size is 2048 bytes');

        $this->class->getPayload();
    }

    /**
     * Test that getPayload throws an exception when the payload is too long and can't be auto adjusted.
     *
     * @covers \ApnsPHP\Message::getPayload
     */
    public function testGetPayloadThrowsExceptionOn(): void
    {
        $this->class->setTitle($this->getLargeString());
        $this->class->setText($this->getLargeString());

        $this->expectException('ApnsPHP\Exception');
        $this->expectExceptionMessage('JSON Payload is too long: 4180 bytes. Maximum size is 2048 bytes.' .
                                      ' The message text can not be auto-adjusted.');

        $this->class->getPayload();
    }

    /**
     * Return a string that is exactly 2090 bytes in size
     *
     * @return string 2090 bytes text
     */
    private function getLargeString()
    {
        $string = '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
        ';

        return $string;
    }
}
