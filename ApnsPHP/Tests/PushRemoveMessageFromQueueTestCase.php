<?php

/**
 * This file contains the PushRemoveMessageFromQueueTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2021 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace ApnsPHP\Tests;

/**
 * This class contains tests for the removeMessageFromQueue function
 *
 * @covers \ApnsPHP\Push
 */
class PushRemoveMessageFromQueueTestCase extends PushTestCase
{
    /**
     * Test that removeMessageFromQueue() throws an exception if the message id is not valid
     *
     * @covers \ApnsPHP\Push::removeMessageFromQueue
     */
    public function testRemoveMessageFromQueueThrowsExceptionOnInvalidMessageID(): void
    {
        $this->expectException('ApnsPHP\Push\Exception');
        $this->expectExceptionMessage('Message ID format is not valid.');

        $method = $this->getReflectionMethod('removeMessageFromQueue');
        $method->invokeArgs($this->class, [ 0 ]);
    }

    /**
     * Test that removeMessageFromQueue() throws an exception if the message id doesn't exist
     *
     * @covers \ApnsPHP\Push::removeMessageFromQueue
     */
    public function testRemoveMessageFromQueueThrowsExceptionOnMissingMessageID(): void
    {
        $this->expectException('ApnsPHP\Push\Exception');
        $this->expectExceptionMessage('The Message ID 1 does not exists.');

        $method = $this->getReflectionMethod('removeMessageFromQueue');
        $method->invokeArgs($this->class, [ 1 ]);
    }

    /**
     * Test that removeMessageFromQueue() removes the message from the queue
     *
     * @covers \ApnsPHP\Push::removeMessageFromQueue
     */
    public function testRemoveMessageFromQueueRemovesMessageFromQueue(): void
    {
        $queue = [
            1 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ],
            2 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ]
        ];

        $this->setReflectionPropertyValue('messageQueue', $queue);

        $method = $this->getReflectionMethod('removeMessageFromQueue');
        $method->invokeArgs($this->class, [ 1 ]);

        $errors       = $this->getReflectionProperty('errors')->getValue($this->class);
        $messageQueue = $this->getReflectionProperty('messageQueue')->getValue($this->class);

        $this->assertArrayEmpty($errors);
        $this->assertEquals([ 2 => $queue[2] ], $messageQueue);
    }

    /**
     * Test that removeMessageFromQueue() removes the message from the queue and adds it to the error array
     *
     * @covers \ApnsPHP\Push::removeMessageFromQueue
     */
    public function testRemoveMessageFromQueueRemovesMessageFromQueueAndAddsError(): void
    {
        $queue = [
            1 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ],
            2 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ]
        ];

        $this->setReflectionPropertyValue('messageQueue', $queue);

        $method = $this->getReflectionMethod('removeMessageFromQueue');
        $method->invokeArgs($this->class, [ 1, true ]);

        $errors       = $this->getReflectionProperty('errors')->getValue($this->class);
        $messageQueue = $this->getReflectionProperty('messageQueue')->getValue($this->class);

        $this->assertEquals([ 1 => $queue[1] ], $errors);
        $this->assertEquals([ 2 => $queue[2] ], $messageQueue);
    }
}
