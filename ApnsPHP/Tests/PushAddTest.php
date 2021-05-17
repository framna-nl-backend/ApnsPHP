<?php

/**
 * This file contains the PushAddTest class.
 *
 * @package ApnsPHP
 * @author  Martijn van Berkum <m.vanberkum@m2mobi.com>
 */

namespace ApnsPHP\Tests;

/**
 * This class contains tests for the add function
 *
 * @covers \ApnsPHP\Push
 */
class PushAddTest extends PushTest
{

    /**
     * Test that add() successfully adds one message
     *
     * @covers \ApnsPHP\Push::add
     */
    public function testAddOneMessage()
    {
        $this->message->expects($this->once())
                      ->method('getPayLoad')
                      ->will($this->returnValue('payload'));

        $this->message->expects($this->once())
                      ->method('getRecipientsNumber')
                      ->will($this->returnValue(1));

        $this->message->expects($this->once())
                      ->method('selfForRecipient')
                      ->with(0)
                      ->will($this->returnValue($this->message));

        $this->class->add($this->message);

        $queue = $this->get_accessible_reflection_property('messageQueue')->getValue($this->class);

        $this->assertEquals($this->message, $queue[1]['MESSAGE']);
    }

    /**
     * Test that add() successfully adds multiple messages
     *
     * @covers \ApnsPHP\Push::add
     */
    public function testAddMultipleMessages()
    {
        $messages = [
            1 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ],
            2 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ],
            3 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ],
            4 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ]
        ];

        $this->message->expects($this->once())
                      ->method('getPayLoad')
                      ->will($this->returnValue('payload'));

        $this->message->expects($this->once())
                      ->method('getRecipientsNumber')
                      ->will($this->returnValue(4));

        $this->message->expects($this->exactly(4))
                      ->method('selfForRecipient')
                      ->withConsecutive([0], [1], [2], [3])
                      ->will($this->returnValue($this->message));

        $this->class->add($this->message);

        $queue = $this->get_accessible_reflection_property('messageQueue')->getValue($this->class);

        $this->assertEquals($messages, $queue);
    }

    /**
     * Test that add() does nothing if there are no recipients
     *
     * @covers \ApnsPHP\Push::add
     */
    public function testAddDoesNothing()
    {
        $this->message->expects($this->once())
                      ->method('getPayLoad')
                      ->will($this->returnValue('payload'));

        $this->message->expects($this->once())
                      ->method('getRecipientsNumber')
                      ->will($this->returnValue(0));

        $this->class->add($this->message);

        $queue = $this->get_accessible_reflection_property('messageQueue')->getValue($this->class);

        $this->assertArrayEmpty($queue);
    }
}
