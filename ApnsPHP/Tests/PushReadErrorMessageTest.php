<?php

/**
 * This file contains the PushReadErrorMessageTest class.
 *
 * @package ApnsPHP
 * @author  Martijn van Berkum <m.vanberkum@m2mobi.com>
 */

namespace ApnsPHP\Tests;

/**
 * This class contains tests for the readErrorMessage function
 *
 * @covers \ApnsPHP\Push
 */
class PushReadErrorMessageTest extends PushTest
{

    /**
     * Test that readErrorMessage() returns if there is no error message
     *
     * @covers \ApnsPHP\Push::readErrorMessage
     */
    public function testUpdateQueueReturnsOnNoError()
    {
        $this->set_reflection_property_value('hSocket', false);

        $method = $this->get_accessible_reflection_method('readErrorMessage');
        $result = $method->invoke($this->class);

        $this->assertEmpty($result);
    }

    /**
     * Test that readErrorMessage() returns on empty error response
     *
     * @covers \ApnsPHP\Push::readErrorMessage
     */
    public function testUpdateQueueReturnsOnEmptyErrorResponse()
    {
        $this->class->expects($this->once())
                    ->method('parseErrorMessage')
                    ->will($this->returnValue(null));

        $this->mock_function('fread', function () {
            return 'string';
        });

        $method = $this->get_accessible_reflection_method('readErrorMessage');
        $result = $method->invoke($this->class);

        $this->unmock_function('fread');

        $this->assertEmpty($result);
    }

    /**
     * Test that readErrorMessage() returns on incomplete error response
     *
     * @covers \ApnsPHP\Push::readErrorMessage
     */
    public function testUpdateQueueReturnsOnIncompleteErrorResponse()
    {
        $response = [ 'invalidResponse' => 1 ];

        $this->class->expects($this->once())
                    ->method('parseErrorMessage')
                    ->will($this->returnValue($response));

        $this->mock_function('fread', function () {
            return 'string';
        });

        $method = $this->get_accessible_reflection_method('readErrorMessage');
        $result = $method->invoke($this->class);

        $this->unmock_function('fread');

        $this->assertEmpty($result);
    }

    /**
     * Test that readErrorMessage() returns on invalid error response
     *
     * @covers \ApnsPHP\Push::readErrorMessage
     */
    public function testUpdateQueueReturnsOnInvalidErrorResponse()
    {
        $this->mock_function('fread', function () {
            return 'string';
        });

        $response = [ 'command' => 1, 'statusCode' => 4, 'identifier' => 1 ];

        $this->class->expects($this->once())
                    ->method('parseErrorMessage')
                    ->will($this->returnValue($response));

        $method = $this->get_accessible_reflection_method('readErrorMessage');
        $result = $method->invoke($this->class);

        $this->unmock_function('fread');

        $this->assertEmpty($result);
    }

    /**
     * Test that readErrorMessage() returns and error response with an unknown status message
     *
     * @covers \ApnsPHP\Push::readErrorMessage
     */
    public function testUpdateQueueReturnsWithUnknownStatusMessage()
    {
        $this->mock_function('fread', function () {
            return 'string';
        });
        $this->mock_function('time', function () {
            return 1620029695;
        });

        $response = [ 'command' => 8, 'statusCode' => 90, 'identifier' => 1 ];

        $expectedResult = [
            'command' => 8,
            'statusCode' => 90,
            'identifier' => 1,
            'time' => 1620029695,
            'statusMessage' => 'None (unknown)'
        ];

        $this->class->expects($this->once())
                    ->method('parseErrorMessage')
                    ->will($this->returnValue($response));

        $method = $this->get_accessible_reflection_method('readErrorMessage');
        $result = $method->invoke($this->class);

        $this->unmock_function('fread');
        $this->unmock_function('time');

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test that readErrorMessage() returns and error response with a known status message
     *
     * @covers \ApnsPHP\Push::readErrorMessage
     */
    public function testUpdateQueueReturnsWithStatusMessage()
    {
        $this->mock_function('fread', function () {
            return 'string';
        });
        $this->mock_function('time', function () {
            return 1620029695;
        });

        $response = [ 'command' => 8, 'statusCode' => 4, 'identifier' => 1 ];

        $expectedResult = [
            'command' => 8,
            'statusCode' => 4,
            'identifier' => 1,
            'time' => 1620029695,
            'statusMessage' => 'Missing payload'
        ];

        $this->class->expects($this->once())
                    ->method('parseErrorMessage')
                    ->will($this->returnValue($response));

        $method = $this->get_accessible_reflection_method('readErrorMessage');
        $result = $method->invoke($this->class);

        $this->unmock_function('fread');
        $this->unmock_function('time');

        $this->assertEquals($expectedResult, $result);
    }
}
