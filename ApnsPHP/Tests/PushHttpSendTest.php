<?php

/**
 * This file contains the PushHttpSendTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2021 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace ApnsPHP\Tests;

use ApnsPHP\Environment;
use ApnsPHP\Message\Priority;
use ApnsPHP\Message\PushType;

/**
 * This class contains tests for the httpSend function
 *
 * @covers \ApnsPHP\Push
 */
class PushHttpSendTest extends PushTest
{
    /**
     * Helper function to set the http headers and verify calls to message getters
     * These calls happen when HttpSend is called and can be the same every time
     */
    private function setHttpHeaders(): void
    {
        $this->message->expects($this->exactly(2))
                      ->method('getTopic')
                      ->will($this->returnValue('topic'));

        $this->message->expects($this->exactly(2))
                      ->method('getExpiry')
                      ->will($this->returnValue(10));

        $this->message->expects($this->exactly(2))
                      ->method('getPriority')
                      ->willReturn(Priority::ConsiderPowerUsage);

        $this->message->expects($this->exactly(2))
                      ->method('getCollapseId')
                      ->will($this->returnValue('1'));

        $this->message->expects($this->exactly(2))
                      ->method('getCustomIdentifier')
                      ->will($this->returnValue('7530A828-E58E-433E-A38F-D8042208CF96'));

        $this->message->expects($this->exactly(2))
                      ->method('getPushType')
                      ->will($this->returnValue(PushType::Alert));

        $this->set_reflection_property_value('providerToken', 'jwt');
    }

    /**
     * Test that httpSend() returns false when the curl session fails
     *
     * @covers \ApnsPHP\Push::httpSend
     */
    public function testHttpSendReturnsFalseOnCurlSessionFail(): void
    {
        $this->mock_function('curl_exec', function () {
            return false;
        });

        $this->set_reflection_property_value('environment', Environment::Sandbox);
        $this->set_reflection_property_value('hSocket', curl_init());

        $this->setHttpHeaders();

        $this->message->expects($this->once())
                      ->method('getRecipient')
                      ->will($this->returnValue('recipient'));

        $this->message->expects($this->once())
                      ->method('getPayload')
                      ->will($this->returnValue('payload'));

        $reply = 'reply';

        $method = $this->get_accessible_reflection_method('httpSend');
        $result = $method->invokeArgs($this->class, [ $this->message, &$reply ]);

        $this->assertFalse($result);

        $this->unmock_function('curl_exec');
    }

    /**
     * Test that httpSend() returns false when the curl opts cannot be set
     *
     * @covers \ApnsPHP\Push::httpSend
     */
    public function testHttpSendReturnsFalseOnCurlOptsCannotBeSet(): void
    {
        $this->mock_function('curl_setopt_array', function () {
            return false;
        });

        $this->mock_function('curl_exec', function () {
            return '';
        });

        $this->mock_function('curl_errno', function () {
            return 56;
        });

        $this->mock_function('curl_error', function () {
            return 'OpenSSL SSL_read: error:14094415:SSL routines:ssl3_read_bytes:sslv3 alert certificate expired';
        });

        $this->set_reflection_property_value('environment', Environment::Sandbox);

        $this->setHttpHeaders();

        $this->message->expects($this->once())
                      ->method('getRecipient')
                      ->will($this->returnValue('recipient'));

        $this->message->expects($this->once())
                      ->method('getPayload')
                      ->will($this->returnValue('payload'));

        $reply = 'reply';

        $method = $this->get_accessible_reflection_method('httpSend');
        $result = $method->invokeArgs($this->class, [ $this->message, &$reply ]);

        $this->assertFalse($result);

        $this->unmock_function('curl_setopt_array');
        $this->unmock_function('curl_exec');
    }

    /**
     * Test that httpSend() returns false when the request returns a http code that is not 200(ok)
     *
     * @covers \ApnsPHP\Push::httpSend
     */
    public function testHttpSendReturnsFalseOnRequestFail(): void
    {
        $this->mock_function('curl_setopt_array', function () {
            return true;
        });

        $this->mock_function('curl_exec', function () {
            return '';
        });

        $this->mock_function('curl_getinfo', function () {
            return 500;
        });

        $this->set_reflection_property_value('environment', Environment::Sandbox);

        $this->setHttpHeaders();

        $this->message->expects($this->once())
                      ->method('getRecipient')
                      ->will($this->returnValue('recipient'));

        $this->message->expects($this->once())
                      ->method('getPayload')
                      ->will($this->returnValue('payload'));

        $reply = 'reply';

        $method = $this->get_accessible_reflection_method('httpSend');
        $result = $method->invokeArgs($this->class, [ $this->message, &$reply ]);

        $this->assertFalse($result);

        $this->unmock_function('curl_setopt_array');
        $this->unmock_function('curl_exec');
        $this->unmock_function('curl_getinfo');
    }

    /**
     * Test that httpSend() returns true on success
     *
     * @covers \ApnsPHP\Push::httpSend
     */
    public function testHttpSendReturnsTrueOnSuccess(): void
    {
        $this->mock_function('curl_setopt_array', function () {
            return true;
        });

        $this->mock_function('curl_exec', function () {
            return '';
        });

        $this->mock_function('curl_getinfo', function () {
            return 200;
        });

        $this->set_reflection_property_value('environment', Environment::Sandbox);

        $this->setHttpHeaders();

        $this->message->expects($this->once())
                      ->method('getRecipient')
                      ->will($this->returnValue('recipient'));

        $this->message->expects($this->once())
                      ->method('getPayload')
                      ->will($this->returnValue('payload'));

        $reply = 'reply';

        $method = $this->get_accessible_reflection_method('httpSend');
        $result = $method->invokeArgs($this->class, [ $this->message, &$reply ]);

        $this->assertTrue($result);

        $this->unmock_function('curl_setopt_array');
        $this->unmock_function('curl_exec');
        $this->unmock_function('curl_getinfo');
    }
}
