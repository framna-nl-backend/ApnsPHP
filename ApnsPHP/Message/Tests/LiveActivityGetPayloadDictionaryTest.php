<?php

/**
 * This file contains the LiveActivityGetPayloadDictionaryTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace ApnsPHP\Message\Tests;

use ApnsPHP\Message\LiveActivityEvent;

/**
 * This class contains tests for the getPayloadDictionary function
 *
 * @covers \ApnsPHP\Message
 */
class LiveActivityGetPayloadDictionaryTest extends LiveActivityTestCase
{
    /**
     * Test that getPayloadDictionary returns complete payload
     *
     * @covers \ApnsPHP\Message::getPayloadDictionary
     */
    public function testGetPayloadDictionaryReturnsCompletePayload(): void
    {
        $this->mock_function('time', fn() => 1731944572);

        $this->class->setTitle('Were no strangers to love');
        $this->class->setText('You know the rules, and so do I');
        $this->class->setCategory('something');
        $this->class->setThreadId('thisIsAThreadId');
        $this->class->setCustomProperty('property', 'property');
        $this->class->setCustomProperty('name', 'value');
        $this->class->setTopic('name.push-type.liveactivity');
        $this->class->setEvent(LiveActivityEvent::Start);
        $this->class->setContentState([]);
        $this->class->setAttributes([]);
        $this->class->setAttributesType('Type');
        $this->class->setStaleTimestamp(1);
        $this->class->setDismissTimestamp(2);
        $this->class->setActivityId('some-id');

        $payload = [
            'aps' => [
                'alert' => [
                    'title' => 'Were no strangers to love',
                    'body'  => 'You know the rules, and so do I'
                ],
                'category'          => 'something',
                'thread-id'         => 'thisIsAThreadId',
                'event'             => 'start',
                'timestamp'         => 1731944572,
                'content-state'     => [],
                'stale-date'        => 1,
                'dismissal-date'    => 2,
                'attributes-type'   => 'Type',
                'attributes'        => [],
                'activity-id'       => 'some-id',
            ],
            'property' => 'property',
            'name' => 'value'
        ];

        $result = $this->getReflectionMethod('getPayloadDictionary')
                       ->invoke($this->class);

        $this->assertEquals($payload, $result);
        $this->unmock_function('time');
    }

    /**
     * Test that getPayloadDictionary returns an empty payload if nothing is set
     *
     * @covers \ApnsPHP\Message::getPayloadDictionary
     */
    public function testGetPayloadDictionaryReturnsEmptyPayload(): void
    {
        $this->mock_function('time', fn() => 1731944572);

        $this->class->setEvent(LiveActivityEvent::Start);

        $payload = [
            'aps' => [
                'event'     => 'start',
                'timestamp' => 1731944572
            ]
        ];

        $result = $this->getReflectionMethod('getPayloadDictionary')
                       ->invoke($this->class);

        $this->assertEquals($payload, $result);
        $this->unmock_function('time');
    }
}
