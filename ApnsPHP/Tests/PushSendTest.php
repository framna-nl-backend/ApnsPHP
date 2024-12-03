<?php

/**
 * This file contains the PushSendTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2021 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace ApnsPHP\Tests;

use ApnsPHP\Environment;
use stdClass;

/**
 * This class contains tests for the send function
 *
 * @covers \ApnsPHP\Push
 */
class PushSendTest extends PushTest
{
    /**
     * Test that send() throws an exception if there is no connection to the push notification service
     *
     * @covers \ApnsPHP\Push::send
     */
    public function testSendThrowsExceptionOnNoConnection(): void
    {
        $this->expectException('ApnsPHP\Push\Exception');
        $this->expectExceptionMessage('Not connected to Push Notification Service');

        $this->class->send();
    }

    /**
     * Test that send() throws an exception if there is nothing to send
     *
     * @covers \ApnsPHP\Push::send
     */
    public function testSendThrowsExceptionOnEmptyQueue(): void
    {
        $this->set_reflection_property_value('hSocket', curl_init());

        $this->expectException('ApnsPHP\Push\Exception');
        $this->expectExceptionMessage('No notifications queued to be sent');

        $this->class->send();
    }

    /**
     * Test that send() doesn't retry sending a message if it has an unrecoverable error
     *
     * @covers \ApnsPHP\Push::send
     */
    public function testSendFailsWithoutRetrying(): void
    {
        $this->mock_function('curl_exec', function () {
            return false;
        });
        $this->mock_function('curl_setopt_array', function () {
            return true;
        });
        $this->mock_function('curl_getinfo', function () {
            return 404;
        });
        $this->mock_function('curl_close', function () {
            return null;
        });
        $this->mock_function('curl_errno', function () {
            return 0;
        });
        $this->mock_function('curl_error', function () {
            return '';
        });

        $error = [
            'command' => 8,
            'statusCode' => 4,
            'identifier' => 1,
            'time' => 1620029695,
            'statusMessage' => 'Missing payload'
        ];
        $message = [ 1 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ] ];

        $this->set_reflection_property_value('environment', Environment::Sandbox);
        $this->set_reflection_property_value('hSocket', curl_init());
        $this->set_reflection_property_value('messageQueue', $message);
        $this->set_reflection_property_value('logger', $this->logger);
        $this->set_reflection_property_value('writeInterval', 0);

        $this->logger->expects($this->exactly(6))
                     ->method('info')
                     ->withConsecutive(
                         [ 'Sending messages queue, run #1: 1 message(s) left in queue.' ],
                         [ 'Disconnected.' ],
                         [ 'Trying to initialize HTTP/2 backend...' ],
                         [ 'Initializing HTTP/2 backend with certificate.' ],
                         [ 'Initialized HTTP/2 backend.' ],
                         [ 'Sending messages queue, run #2: 1 message(s) left in queue.' ],
                     );

        $this->logger->expects($this->once())
                     ->method('debug')
                     ->with('Sending message ID 1 [custom identifier: unset] (1/3): 0 bytes.');

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with('Message ID 1 [custom identifier: unset] has an unrecoverable error
                                 (404), removing from queue without retrying...');

        $this->class->send();

        $this->unmock_function('curl_exec');
        $this->unmock_function('curl_setopt_array');
        $this->unmock_function('curl_getinfo');
        $this->unmock_function('curl_close');
    }

    /**
     * Test that send() retries sending a message if it fails
     *
     * @covers \ApnsPHP\Push::send
     */
    public function testSendFailsWithRetrying(): void
    {
        $this->mock_function('curl_exec', function () {
            return false;
        });
        $this->mock_function('curl_setopt_array', function () {
            return true;
        });
        $this->mock_function('curl_getinfo', function () {
            return 429;
        });
        $this->mock_function('curl_close', function () {
            return null;
        });

        $message = [ 1 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ] ];

        $this->set_reflection_property_value('environment', Environment::Sandbox);
        $this->set_reflection_property_value('hSocket', curl_init());
        $this->set_reflection_property_value('messageQueue', $message);
        $this->set_reflection_property_value('logger', $this->logger);
        $this->set_reflection_property_value('writeInterval', 0);

        $this->logger->expects($this->exactly(16))
                     ->method('info')
                     ->withConsecutive(
                         [ 'Sending messages queue, run #1: 1 message(s) left in queue.' ],
                         [ 'Disconnected.' ],
                         [ 'Trying to initialize HTTP/2 backend...' ],
                         [ 'Initializing HTTP/2 backend with certificate.' ],
                         [ 'Initialized HTTP/2 backend.' ],
                         [ 'Sending messages queue, run #2: 1 message(s) left in queue.' ],
                         [ 'Disconnected.' ],
                         [ 'Trying to initialize HTTP/2 backend...' ],
                         [ 'Initializing HTTP/2 backend with certificate.' ],
                         [ 'Initialized HTTP/2 backend.' ],
                         [ 'Sending messages queue, run #3: 1 message(s) left in queue.' ],
                         [ 'Disconnected.' ],
                         [ 'Trying to initialize HTTP/2 backend...' ],
                         [ 'Initializing HTTP/2 backend with certificate.' ],
                         [ 'Initialized HTTP/2 backend.' ],
                         [ 'Sending messages queue, run #4: 1 message(s) left in queue.' ],
                     );

        $this->logger->expects($this->exactly(3))
                     ->method('debug')
                     ->withConsecutive(
                         [ 'Sending message ID 1 [custom identifier: unset] (1/3): 0 bytes.' ],
                         [ 'Sending message ID 1 [custom identifier: unset] (2/3): 0 bytes.' ],
                         [ 'Sending message ID 1 [custom identifier: unset] (3/3): 0 bytes.' ],
                     );

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with('Message ID 1 [custom identifier: unset] has 3 errors, removing from queue...');

        $this->class->send();

        $this->unmock_function('curl_exec');
        $this->unmock_function('curl_setopt_array');
        $this->unmock_function('curl_getinfo');
        $this->unmock_function('curl_close');
    }

    /**
     * Test that send() retries sending a message if it fails
     *
     * @covers \ApnsPHP\Push::send
     */
    public function testSendRemovesWhenNoError(): void
    {
        $this->mock_function('curl_exec', function () {
            return false;
        });
        $this->mock_function('curl_setopt_array', function () {
            return true;
        });
        $this->mock_function('curl_getinfo', function () {
            return 200;
        });
        $this->mock_function('curl_close', function () {
            return null;
        });
        $this->mock_function('curl_errno', function () {
            return 0;
        });
        $this->mock_function('curl_error', function () {
            return '';
        });

        $message = [ 1 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ] ];

        $this->set_reflection_property_value('environment', Environment::Sandbox);
        $this->set_reflection_property_value('hSocket', curl_init());
        $this->set_reflection_property_value('messageQueue', $message);
        $this->set_reflection_property_value('logger', $this->logger);
        $this->set_reflection_property_value('writeInterval', 0);

        $this->logger->expects($this->exactly(7))
                     ->method('info')
                     ->withConsecutive(
                         [ 'Sending messages queue, run #1: 1 message(s) left in queue.' ],
                         [ 'Disconnected.' ],
                         [ 'Trying to initialize HTTP/2 backend...' ],
                         [ 'Initializing HTTP/2 backend with certificate.' ],
                         [ 'Initialized HTTP/2 backend.' ],
                         [ 'Sending messages queue, run #2: 1 message(s) left in queue.' ],
                         [ 'Message ID 1 [custom identifier: unset] has no error (200),
                                 removing from queue...'],
                     );

        $this->logger->expects($this->once())
                     ->method('debug')
                     ->with('Sending message ID 1 [custom identifier: unset] (1/3): 0 bytes.');

        $this->logger->expects($this->never())
                     ->method('warning');

        $this->class->send();

        $this->unmock_function('curl_exec');
        $this->unmock_function('curl_setopt_array');
        $this->unmock_function('curl_getinfo');
        $this->unmock_function('curl_close');
    }

    /**
     * Test that send() Successfully sends a message
     *
     * @covers \ApnsPHP\Push::send
     */
    public function testSendSuccessfullySends(): void
    {
        $this->mock_function('curl_exec', function () {
            return '';
        });
        $this->mock_function('curl_setopt_array', function () {
            return true;
        });
        $this->mock_function('curl_getinfo', function () {
            return 200;
        });
        $this->mock_function('curl_close', function () {
            return null;
        });

        $message = [ 1 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ] ];

        $this->set_reflection_property_value('environment', Environment::Sandbox);
        $this->set_reflection_property_value('hSocket', curl_init());
        $this->set_reflection_property_value('messageQueue', $message);
        $this->set_reflection_property_value('logger', $this->logger);
        $this->set_reflection_property_value('writeInterval', 0);

        $this->logger->expects($this->once())
                     ->method('info')
                     ->with('Sending messages queue, run #1: 1 message(s) left in queue.');

        $this->logger->expects($this->once())
                     ->method('debug')
                     ->with('Sending message ID 1 [custom identifier: unset] (1/3): 0 bytes.');

        $this->class->send();

        $this->unmock_function('curl_exec');
        $this->unmock_function('curl_setopt_array');
        $this->unmock_function('curl_getinfo');
        $this->unmock_function('curl_close');
    }
}
