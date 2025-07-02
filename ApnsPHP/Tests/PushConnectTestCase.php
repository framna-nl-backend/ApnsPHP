<?php

/**
 * This file contains the PushConnectTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2021 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace ApnsPHP\Tests;

use ApnsPHP\Exception;
use stdClass;
use Throwable;

/**
 * This class contains tests for the connect function
 *
 * @covers \ApnsPHP\Push
 */
class PushConnectTestCase extends PushTestCase
{
    /**
     * Test that connect() connects successfully
     *
     * @covers \ApnsPHP\Push::connect
     */
    public function testConnectSuccess(): void
    {
        $this->setReflectionPropertyValue('logger', $this->logger);

        $this->mock_function('curl_setopt_array', fn() => true);

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

        $this->class->connect();

        $this->unmock_function('curl_setopt_array');
    }

    /**
     * Test that connect() throws an exception when failing to connect
     *
     * @covers \ApnsPHP\Push::connect
     */
    public function testConnectThrowsExceptionOnHttpInitFail(): void
    {
        $this->setReflectionPropertyValue('connectRetryInterval', 0);
        $this->setReflectionPropertyValue('logger', $this->logger);

        $this->mock_function('curl_setopt_array', fn() => false);

        $this->logger->expects($this->exactly(4))
                     ->method('error')
                     ->with('Unable to initialize HTTP/2 backend.');

        $expectations = [
            'Trying to initialize HTTP/2 backend...',
            'Initializing HTTP/2 backend with certificate.',
            'Retry to connect (1/3)...',
            'Trying to initialize HTTP/2 backend...',
            'Initializing HTTP/2 backend with certificate.',
            'Retry to connect (2/3)...',
            'Trying to initialize HTTP/2 backend...',
            'Initializing HTTP/2 backend with certificate.',
            'Retry to connect (3/3)...',
            'Trying to initialize HTTP/2 backend...',
            'Initializing HTTP/2 backend with certificate.',
        ];

        $invokedCount = self::exactly(count($expectations));

        $this->logger->expects($invokedCount)
                     ->method('info')
                     ->willReturnCallback(function ($parameters) use ($invokedCount, $expectations) {
                         $currentInvocationCount = $invokedCount->numberOfInvocations();
                         $currentExpectation = $expectations[$currentInvocationCount - 1];

                         $this->assertSame($currentExpectation, $parameters);
                     });

        $this->expectException('ApnsPHP\Exception');
        $this->expectExceptionMessage('Unable to initialize HTTP/2 backend.');

        try {
            $this->class->connect();
        } catch (Throwable $e) {
            $this->unmock_function('curl_setopt_array');

            throw $e;
        }
    }
}
