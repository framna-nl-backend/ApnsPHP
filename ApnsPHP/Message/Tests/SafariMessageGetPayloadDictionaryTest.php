<?php

/**
 * This file contains the SafariMessageGetPayloadDictionaryTest class.
 *
 * @package ApnsPHP
 * @author  Martijn van Berkum <m.vanberkum@m2mobi.com>
 */

namespace ApnsPHP\Message\Tests;

/**
 * This class contains tests for the getPayloadDictionary function
 *
 * @covers \ApnsPHP\Message\SafariMessage
 */
class SafariMessageGetPayloadDictionaryTest extends SafariMessageTest
{

    /**
     * Test that getPayloadDictionary returns complete payload
     *
     * @covers \ApnsPHP\Message\SafariMessage::getPayloadDictionary
     */
    public function testGetPayloadDictionaryReturnsCompletePayload(): void
    {
        $this->class->setTitle('Never gonna give you up');
        $this->class->setText('Never gonna let you down');
        $this->class->setAction('label');
        $this->class->setUrlArgs([ 'value', 'value' ]);

        $payload = [
            'aps' => [
                'alert' => [
                    'title'  => 'Never gonna give you up',
                    'body'   => 'Never gonna let you down',
                    'action' => 'label'
                ],
                'url-args' => [ 'value', 'value' ]
            ]
        ];

        $result = $this->get_accessible_reflection_method('getPayloadDictionary')->invoke($this->class);

        $this->assertEquals($payload, $result);
    }

    /**
     * Test that getPayloadDictionary returns empty payload if nothing is set
     *
     * @covers \ApnsPHP\Message\SafariMessage::getPayloadDictionary
     */
    public function testGetPayloadDictionaryReturnsEmptyPayload(): void
    {
        $payload = [ 'aps' => [ 'alert' => [] ] ];

        $result = $this->get_accessible_reflection_method('getPayloadDictionary')->invoke($this->class);

        $this->assertEquals($payload, $result);
    }
}
