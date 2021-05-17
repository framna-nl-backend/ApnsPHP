<?php

/**
 * This file contains the PushRemoveMessageFromQueueTest class.
 *
 * @package ApnsPHP
 * @author  Martijn van Berkum <m.vanberkum@m2mobi.com>
 */

namespace ApnsPHP\Tests;

/**
 * This class contains tests for the removeMessageFromQueue function
 *
 * @covers \ApnsPHP\Push
 */
class PushRemoveMessageFromQueueTest extends PushTest
{

    /**
     * Test that removeMessageFromQueue() throws an exception if the message id is not valid
     *
     * @covers \ApnsPHP\Push::removeMessageFromQueue
     */
    public function testRemoveMessageFromQueueThrowsExceptionOnInvalidMessageID()
    {
        $this->expectException('ApnsPHP\Push\Exception');
        $this->expectExceptionMessage('Message ID format is not valid.');

        $method = $this->get_accessible_reflection_method('removeMessageFromQueue');
        $method->invokeArgs($this->class, [ 0 ]);
    }

    /**
     * Test that removeMessageFromQueue() throws an exception if the message id doesn't exist
     *
     * @covers \ApnsPHP\Push::removeMessageFromQueue
     */
    public function testRemoveMessageFromQueueThrowsExceptionOnMissingMessageID()
    {
        $this->expectException('ApnsPHP\Push\Exception');
        $this->expectExceptionMessage('The Message ID 1 does not exists.');

        $method = $this->get_accessible_reflection_method('removeMessageFromQueue');
        $method->invokeArgs($this->class, [ 1 ]);
    }

    /**
     * Test that removeMessageFromQueue() removes the message from the queue
     *
     * @covers \ApnsPHP\Push::removeMessageFromQueue
     */
    public function testRemoveMessageFromQueueRemovesMessageFromQueue()
    {
        $queue = [
            1 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ],
            2 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ]
        ];

        $this->set_reflection_property_value('messageQueue', $queue);

        $method = $this->get_accessible_reflection_method('removeMessageFromQueue');
        $method->invokeArgs($this->class, [ 1 ]);

        $errors       = $this->get_accessible_reflection_property('errors')->getValue($this->class);
        $messageQueue = $this->get_accessible_reflection_property('messageQueue')->getValue($this->class);

        $this->assertArrayEmpty($errors);
        $this->assertEquals([ 2 => $queue[2] ], $messageQueue);
    }

    /**
     * Test that removeMessageFromQueue() removes the message from the queue and adds it to the error array
     *
     * @covers \ApnsPHP\Push::removeMessageFromQueue
     */
    public function testRemoveMessageFromQueueRemovesMessageFromQueueAndAddsError()
    {
        $queue = [
            1 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ],
            2 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ]
        ];

        $this->set_reflection_property_value('messageQueue', $queue);

        $method = $this->get_accessible_reflection_method('removeMessageFromQueue');
        $method->invokeArgs($this->class, [ 1, true ]);

        $errors       = $this->get_accessible_reflection_property('errors')->getValue($this->class);
        $messageQueue = $this->get_accessible_reflection_property('messageQueue')->getValue($this->class);

        $this->assertEquals([ 1 => $queue[1] ], $errors);
        $this->assertEquals([ 2 => $queue[2] ], $messageQueue);
    }
}
