<?php

/**
 * This file contains the PushAddTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2021 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace ApnsPHP\Tests;

/**
 * This class contains tests for the add function
 *
 * @covers \ApnsPHP\Push
 */
class PushAddTest extends PushTest
{
    /**
     * Test that add() successfully adds one message
     *
     * @covers \ApnsPHP\Push::add
     */
    public function testAddOneMessage(): void
    {
        $this->message->expects($this->once())
                      ->method('getPayLoad')
                      ->will($this->returnValue('payload'));

        $this->message->expects($this->once())
                      ->method('getRecipientsCount')
                      ->will($this->returnValue(1));

        $this->message->expects($this->once())
                      ->method('selfForRecipient')
                      ->with(0)
                      ->will($this->returnValue($this->message));

        $this->class->add($this->message);

        $queue = $this->get_reflection_property('messageQueue')->getValue($this->class);

        $this->assertEquals($this->message, $queue[1]['MESSAGE']);
    }

    /**
     * Test that add() successfully adds multiple messages
     *
     * @covers \ApnsPHP\Push::add
     */
    public function testAddMultipleMessages(): void
    {
        $messages = [
            1 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ],
            2 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ],
            3 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ],
            4 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ]
        ];

        $this->message->expects($this->once())
                      ->method('getPayLoad')
                      ->will($this->returnValue('payload'));

        $this->message->expects($this->once())
                      ->method('getRecipientsCount')
                      ->will($this->returnValue(4));

        $this->message->expects($this->exactly(4))
                      ->method('selfForRecipient')
                      ->withConsecutive([0], [1], [2], [3])
                      ->will($this->returnValue($this->message));

        $this->class->add($this->message);

        $queue = $this->get_reflection_property('messageQueue')->getValue($this->class);

        $this->assertEquals($messages, $queue);
    }

    /**
     * Test that add() does nothing if there are no recipients
     *
     * @covers \ApnsPHP\Push::add
     */
    public function testAddDoesNothing(): void
    {
        $this->message->expects($this->once())
                      ->method('getPayLoad')
                      ->will($this->returnValue('payload'));

        $this->message->expects($this->once())
                      ->method('getRecipientsCount')
                      ->will($this->returnValue(0));

        $this->class->add($this->message);

        $queue = $this->get_reflection_property('messageQueue')->getValue($this->class);

        $this->assertArrayEmpty($queue);
    }
}
