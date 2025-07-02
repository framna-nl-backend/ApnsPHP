<?php

/**
 * This file contains the PushUpdateQueueTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2021 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace ApnsPHP\Tests;

/**
 * This class contains tests for the updateQueue function
 *
 * @covers \ApnsPHP\Push
 */
class PushUpdateQueueTestCase extends PushTestCase
{
    /**
     * Test that updateQueue() returns false if there is no errorMessage
     *
     * @covers \ApnsPHP\Push::updateQueue
     */
    public function testUpdateQueueReturnsFalse(): void
    {
        $method = $this->getReflectionMethod('updateQueue');
        $result = $method->invoke($this->class);

        $this->assertFalse($result);
    }

    /**
     * Test that updateQueue() succeeds with an errorMessage parameter
     *
     * @covers \ApnsPHP\Push::updateQueue
     */
    public function testUpdateQueueSucceedsWithErrorMessageParameter(): void
    {
        $errorMessage = [
            'identifier' => 3,
            'time' => 1620029695,
            'statusCode' => 4 ,
            'statusMessage' => 'Missing payload'
        ];

        $queue = [
            1 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ],
            2 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ],
            3 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ]
        ];

        $resultMessage = [ 3 => [ 'MESSAGE' => $this->message, 'ERRORS' => [ $errorMessage ] ]];

        $this->setReflectionPropertyValue('messageQueue', $queue);
        $this->setReflectionPropertyValue('logger', $this->logger);

        $expectations = [
            'Trying to initialize HTTP/2 backend...',
            'Initializing HTTP/2 backend with certificate.',
            'Initialized HTTP/2 backend.',
        ];

        $invokedCount = self::exactly(count($expectations));

        $this->logger->expects($invokedCount)
                     ->method('info')
                     ->willReturnCallback(function ($parameters) use ($invokedCount, $expectations) {
                         $currentInvocationCount = $invokedCount->numberOfInvocations();
                         $currentExpectation = $expectations[$currentInvocationCount - 1];

                         $this->assertSame($currentExpectation, $parameters);
                     });

        $this->logger->expects($this->never())
                     ->method('warning');

        $this->logger->expects($this->once())
                     ->method('error')
                     ->with('Unable to send message ID 3: Missing payload (4).');

        $method = $this->getReflectionMethod('updateQueue');
        $result = $method->invokeArgs($this->class, [ $errorMessage ]);

        $messageQueue  = $this->getReflectionPropertyValue('messageQueue');

        $this->assertTrue($result);
        $this->assertEquals($resultMessage, $messageQueue);
    }

    /**
     * Test that updateQueue() succeeds with an errorMessage parameter
     *
     * @covers \ApnsPHP\Push::updateQueue
     */
    public function testUpdateQueueDoesNotDeleteUnsentMessages(): void
    {
        $errorMessage = [
            'identifier' => 2,
            'time' => 1620029695,
            'statusCode' => 4 ,
            'statusMessage' => 'Missing payload'
        ];

        $queue = [
            1 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ],
            3 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ],
            4 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ]
        ];

        $this->setReflectionPropertyValue('messageQueue', $queue);
        $this->setReflectionPropertyValue('logger', $this->logger);

        $expectations = [
            'Trying to initialize HTTP/2 backend...',
            'Initializing HTTP/2 backend with certificate.',
            'Initialized HTTP/2 backend.',
        ];

        $invokedCount = self::exactly(count($expectations));

        $this->logger->expects($invokedCount)
                     ->method('info')
                     ->willReturnCallback(function ($parameters) use ($invokedCount, $expectations) {
                         $currentInvocationCount = $invokedCount->numberOfInvocations();
                         $currentExpectation = $expectations[$currentInvocationCount - 1];

                         $this->assertSame($currentExpectation, $parameters);
                     });

        $this->logger->expects($this->never())
                     ->method('warning');

        $this->logger->expects($this->once())
                     ->method('error')
                     ->with('Unable to send message ID 2: Missing payload (4).');

        $method = $this->getReflectionMethod('updateQueue');
        $result = $method->invoke($this->class, $errorMessage);

        $messageQueue  = $this->getReflectionProperty('messageQueue')->getValue($this->class);

        $this->assertTrue($result);
        $this->assertEquals([ 3 => $queue[3], 4 => $queue[4] ], $messageQueue);
    }
}
