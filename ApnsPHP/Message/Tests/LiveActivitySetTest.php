<?php

/**
 * This file contains the LiveActivitySetTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace ApnsPHP\Message\Tests;

use ApnsPHP\Message\LiveActivityEvent;
use ApnsPHP\Message\PushType;
use RuntimeException;
use UnexpectedValueException;

/**
 * This class contains tests for the setter functions
 *
 * @covers \ApnsPHP\Message
 */
class LiveActivitySetTest extends LiveActivityTestBase
{
    /**
     * Get events to test with
     *
     * @return array<string,LiveActivityEvent[]> Arguments mapped to the event name
     */
    public static function eventDataProvider(): array
    {
        return [
            LiveActivityEvent::Start->value  => [LiveActivityEvent::Start],
            LiveActivityEvent::Update->value => [LiveActivityEvent::Update],
            LiveActivityEvent::End->value    => [LiveActivityEvent::End],
        ];
    }

    /**
     * Test that setPushType() fails.
     *
     * @covers \ApnsPHP\Message\LiveActivity::setPushType
     */
    public function testSetPushTypeFails(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Push type is enforced by the class!");

        $this->class->setPushType(PushType::LiveActivity);
    }

    /**
     * Test that setTopic() sets the activity topic correctly.
     *
     * @covers \ApnsPHP\Message\LiveActivity::setTopic
     */
    public function testSetTopicFailsOnInvalidTopic(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage("Topic 'test' does not include '.push-type.liveactivity'!");

        $this->class->setTopic('test');

        $this->assertPropertySame('topic', 'test');
    }

    /**
     * Test that setTopic() sets the activity topic.
     *
     * @covers \ApnsPHP\Message\LiveActivity::setTopic
     */
    public function testSetTopic(): void
    {
        $this->class->setTopic('test.push-type.liveactivity');

        $this->assertPropertySame('topic', 'test.push-type.liveactivity');
    }

    /**
     * Test that setEvent() sets the activity event.
     *
     * @dataProvider eventDataProvider
     *
     * @param LiveActivityEvent $event The event to test with
     *
     * @covers \ApnsPHP\Message\LiveActivity::setEvent
     */
    public function testSetEvent(LiveActivityEvent $event): void
    {
        $this->class->setEvent($event);

        $this->assertPropertySame('event', $event);
    }

    /**
     * Test that setAttributes() sets the activity attributes.
     *
     * @covers \ApnsPHP\Message\LiveActivity::setAttributes
     */
    public function testSetAttributes(): void
    {
        $this->class->setAttributes([]);
        $this->assertPropertySame('attributes', []);

        $object = (object) [];
        $this->class->setAttributes($object);
        $this->assertPropertySame('attributes', $object);
    }

    /**
     * Test that testSetAttributesType() sets the activity attribute type.
     *
     * @covers \ApnsPHP\Message\LiveActivity::setAttributesType
     */
    public function testSetAttributesType(): void
    {
        $this->class->setAttributesType('SomeType');
        $this->assertPropertySame('attributes_type', 'SomeType');
    }

    /**
     * Test that setStaleTime() sets the time the activity goes stale.
     *
     * @covers \ApnsPHP\Message\LiveActivity::setStaleTimestamp
     */
    public function testSetStaleTime(): void
    {
        $this->class->setStaleTimestamp(1);
        $this->assertPropertySame('stale_timestamp', 1);
    }

    /**
     * Test that setDismissTime() sets the time the activity dismisses.
     *
     * @covers \ApnsPHP\Message\LiveActivity::setDismissTimestamp
     */
    public function testSetDismissTime(): void
    {
        $this->class->setDismissTimestamp(1);
        $this->assertPropertySame('dismiss_timestamp', 1);
    }

    /**
     * Test that setActivityId() sets the time the activity dismisses.
     *
     * @covers \ApnsPHP\Message\LiveActivity::setActivityId
     */
    public function testSetActivityId(): void
    {
        $this->class->setActivityId('some-id');
        $this->assertPropertySame('activityId', 'some-id');
    }

    /**
     * Test that setContentState() sets the time the activity dismisses.
     *
     * @covers \ApnsPHP\Message\LiveActivity::setContentState
     */
    public function testSetContentState(): void
    {
        $this->class->setContentState([]);
        $this->assertPropertySame('state', []);

        $object = (object) [];
        $this->class->setContentState($object);
        $this->assertPropertySame('state', $object);
    }
}
