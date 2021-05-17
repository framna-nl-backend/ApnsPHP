<?php

/**
 * This file contains the PushHttpSendTest class.
 *
 * @package ApnsPHP
 * @author  Martijn van Berkum <m.vanberkum@m2mobi.com>
 */

namespace ApnsPHP\Tests;

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
    private function setHttpHeaders()
    {
        $this->message->expects($this->exactly(2))
                      ->method('getTopic')
                      ->will($this->returnValue('topic'));

        $this->message->expects($this->exactly(2))
                      ->method('getExpiry')
                      ->will($this->returnValue(10));

        $this->message->expects($this->exactly(2))
                      ->method('getPriority')
                      ->will($this->returnValue(5));

        $this->message->expects($this->exactly(2))
                      ->method('getCollapseId')
                      ->will($this->returnValue(1));

        $this->message->expects($this->exactly(2))
                      ->method('getCustomIdentifier')
                      ->will($this->returnValue('7530A828-E58E-433E-A38F-D8042208CF96'));

        $this->set_reflection_property_value('providerToken', 'jwt');
    }

    /**
     * Test that httpSend() returns false when the curl session fails
     *
     * @covers \ApnsPHP\Push::httpSend
     */
    public function testHttpSendReturnsFalseOnCurlSessionFail()
    {
        $this->mock_function('curl_exec', function () {
            return false;
        });

        $this->set_reflection_property_value('environment', 1);
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
    public function testHttpSendReturnsFalseOnCurlOptsCannotBeSet()
    {
        $this->mock_function('curl_setopt_array', function () {
            return false;
        });

        $this->mock_function('curl_exec', function () {
            return true;
        });

        $this->set_reflection_property_value('environment', 1);

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
    public function testHttpSendReturnsFalseOnRequestFail()
    {
        $this->mock_function('curl_setopt_array', function () {
            return true;
        });

        $this->mock_function('curl_exec', function () {
            return true;
        });

        $this->mock_function('curl_getinfo', function () {
            return 500;
        });

        $this->set_reflection_property_value('environment', 1);

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
    public function testHttpSendReturnsTrueOnSuccess()
    {
        $this->mock_function('curl_setopt_array', function () {
            return true;
        });

        $this->mock_function('curl_exec', function () {
            return true;
        });

        $this->mock_function('curl_getinfo', function () {
            return 200;
        });

        $this->set_reflection_property_value('environment', 1);

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
