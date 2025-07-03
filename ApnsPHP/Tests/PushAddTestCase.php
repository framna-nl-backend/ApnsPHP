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
class PushAddTestCase extends PushTestCase
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
                      ->willReturn('payload');

        $this->message->expects($this->once())
                      ->method('getRecipientsCount')
                      ->willReturn(1);

        $this->message->expects($this->once())
                      ->method('selfForRecipient')
                      ->with(0)
                      ->willReturn($this->message);

        $this->class->add($this->message);

        $queue = $this->getReflectionProperty('messageQueue')->getValue($this->class);

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
                      ->willReturn('payload');

        $this->message->expects($this->once())
                      ->method('getRecipientsCount')
                      ->willReturn(4);

        $expectations = [0, 1, 2, 3];

        $invokedCount = self::exactly(count($expectations));

        $this->message->expects($invokedCount)
                      ->method('selfForRecipient')
                      ->willReturnCallback(function ($parameters) use ($invokedCount, $expectations) {
                          $currentInvocationCount = $invokedCount->numberOfInvocations();
                          $currentExpectation = $expectations[$currentInvocationCount - 1];

                          $this->assertSame($currentExpectation, $parameters);

                          return $this->message;
                      });

        $this->class->add($this->message);

        $queue = $this->getReflectionProperty('messageQueue')->getValue($this->class);

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
                      ->willReturn('payload');

        $this->message->expects($this->once())
                      ->method('getRecipientsCount')
                      ->willReturn(0);

        $this->class->add($this->message);

        $queue = $this->getReflectionProperty('messageQueue')->getValue($this->class);

        $this->assertArrayEmpty($queue);
    }
}
