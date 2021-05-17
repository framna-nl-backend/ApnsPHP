<?php

/**
 * This file contains the PushUpdateQueueTest class.
 *
 * @package ApnsPHP
 * @author  Martijn van Berkum <m.vanberkum@m2mobi.com>
 */

namespace ApnsPHP\Tests;

/**
 * This class contains tests for the updateQueue function
 *
 * @covers \ApnsPHP\Push
 */
class PushUpdateQueueTest extends PushTest
{

    /**
     * Test that updateQueue() returns false if there is no errorMessage
     *
     * @covers \ApnsPHP\Push::updateQueue
     */
    public function testUpdateQueueReturnsFalse()
    {
        $this->class->expects($this->once())
                    ->method('readErrorMessage')
                    ->will($this->returnValue(null));

        $method = $this->get_accessible_reflection_method('updateQueue');
        $result = $method->invoke($this->class);

        $this->assertFalse($result);
    }

    /**
     * Test that updateQueue() succeeds without an errorMessage parameter
     *
     * @covers \ApnsPHP\Push::updateQueue
     */
    public function testUpdateQueueSucceedsWithoutErrorMessageParameter()
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

        $resultMessage = [ 3 => [ 'MESSAGE' => $this->message, 'ERRORS' => [$errorMessage] ]];

        $this->set_reflection_property_value('messageQueue', $queue);

        $this->class->expects($this->once())
                    ->method('readErrorMessage')
                    ->will($this->returnValue($errorMessage));

        $this->class->expects($this->once())
                    ->method('logger')
                    ->will($this->returnValue($this->logger));

        $this->logger->expects($this->once())
                     ->method('error')
                     ->with('Unable to send message ID 3: Missing payload (4).');

        $this->class->expects($this->once())
                    ->method('disconnect')
                    ->will($this->returnValue(true));

        $this->class->expects($this->once())
                    ->method('connect');

        $method = $this->get_accessible_reflection_method('updateQueue');
        $result = $method->invoke($this->class);

        $messageQueue  = $this->get_accessible_reflection_property('messageQueue')->getValue($this->class);

        $this->assertTrue($result);
        $this->assertEquals($resultMessage, $messageQueue);
    }

    /**
     * Test that updateQueue() succeeds with an errorMessage parameter
     *
     * @covers \ApnsPHP\Push::updateQueue
     */
    public function testUpdateQueueSucceedsWithErrorMessageParameter()
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

        $resultMessage = [ 3 => [ 'MESSAGE' => $this->message, 'ERRORS' => [$errorMessage] ]];

        $this->set_reflection_property_value('messageQueue', $queue);

        $this->class->expects($this->once())
                    ->method('readErrorMessage')
                    ->will($this->returnValue($errorMessage));

        $this->class->expects($this->once())
                    ->method('logger')
                    ->will($this->returnValue($this->logger));

        $this->logger->expects($this->once())
                     ->method('error')
                     ->with('Unable to send message ID 3: Missing payload (4).');

        $this->class->expects($this->once())
                    ->method('disconnect')
                    ->will($this->returnValue(true));

        $this->class->expects($this->once())
                    ->method('connect');

        $method = $this->get_accessible_reflection_method('updateQueue');
        $result = $method->invokeArgs($this->class, [ $errorMessage ]);

        $messageQueue  = $this->get_accessible_reflection_property('messageQueue')->getValue($this->class);

        $this->assertTrue($result);
        $this->assertEquals($resultMessage, $messageQueue);
    }

    /**
     * Test that updateQueue() succeeds with an errorMessage parameter
     *
     * @covers \ApnsPHP\Push::updateQueue
     */
    public function testUpdateQueueDoesNotDeleteUnsentMessages()
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

        $this->set_reflection_property_value('messageQueue', $queue);

        $this->class->expects($this->once())
                    ->method('readErrorMessage')
                    ->will($this->returnValue($errorMessage));

        $this->class->expects($this->once())
                    ->method('logger')
                    ->will($this->returnValue($this->logger));

        $this->logger->expects($this->once())
                     ->method('error')
                     ->with('Unable to send message ID 2: Missing payload (4).');

        $this->class->expects($this->once())
                    ->method('disconnect')
                    ->will($this->returnValue(true));

        $this->class->expects($this->once())
                    ->method('connect');

        $method = $this->get_accessible_reflection_method('updateQueue');
        $result = $method->invoke($this->class);

        $messageQueue  = $this->get_accessible_reflection_property('messageQueue')->getValue($this->class);

        $this->assertTrue($result);
        $this->assertEquals([ 3 => $queue[3], 4 => $queue[4] ], $messageQueue);
    }
}
