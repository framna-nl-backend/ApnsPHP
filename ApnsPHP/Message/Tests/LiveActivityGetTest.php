<?php

/**
 * This file contains the LiveActivityGetTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace ApnsPHP\Message\Tests;

use ApnsPHP\Message\LiveActivityEvent;

/**
 * This class contains tests for the getter functions
 *
 * @covers \ApnsPHP\Message
 */
class LiveActivityGetTest extends LiveActivityTestBase
{
    /**
     * Test that getEvent() gets the activity event.
     *
     * @covers \ApnsPHP\Message\LiveActivity::getEvent
     */
    public function testGetEvent(): void
    {
        $this->set_reflection_property_value('event', LiveActivityEvent::Start);

        $value = $this->class->getEvent();

        $this->assertSame(LiveActivityEvent::Start, $value);
    }

    /**
     * Test that getAttributes() gets the activity attributes.
     *
     * @covers \ApnsPHP\Message\LiveActivity::getAttributes
     */
    public function testGetAttributes(): void
    {
        $this->set_reflection_property_value('attributes', []);

        $value = $this->class->getAttributes();

        $this->assertSame([], $value);
    }

    /**
     * Test that getAttributes() gets the activity attributes type.
     *
     * @covers \ApnsPHP\Message\LiveActivity::getAttributesType
     */
    public function testGetAttributesType(): void
    {
        $this->set_reflection_property_value('attributes_type', 'Type');

        $value = $this->class->getAttributesType();

        $this->assertSame('Type', $value);
    }

    /**
     * Test that getStaleTime() gets the activity stale time.
     *
     * @covers \ApnsPHP\Message\LiveActivity::getStaleTimestamp
     */
    public function testGetStaleTime(): void
    {
        $this->set_reflection_property_value('stale_timestamp', 1);

        $value = $this->class->getStaleTimestamp();

        $this->assertSame(1, $value);
    }

    /**
     * Test that getDismissTime() gets the activity stale time.
     *
     * @covers \ApnsPHP\Message\LiveActivity::getDismissTimestamp
     */
    public function testGetDismissTime(): void
    {
        $this->set_reflection_property_value('dismiss_timestamp', 1);

        $value = $this->class->getDismissTimestamp();

        $this->assertSame(1, $value);
    }

    /**
     * Test that getActivityId() gets the activity id.
     *
     * @covers \ApnsPHP\Message\LiveActivity::getActivityId
     */
    public function testGetActivityId(): void
    {
        $this->set_reflection_property_value('activityId', 'some-id');

        $value = $this->class->getActivityId();

        $this->assertSame('some-id', $value);
    }

    /**
     * Test that getContentState() gets the activity state.
     *
     * @covers \ApnsPHP\Message\LiveActivity::getContentState
     */
    public function testGetContentState(): void
    {
        $this->set_reflection_property_value('state', []);

        $value = $this->class->getContentState();

        $this->assertSame([], $value);
    }
}
