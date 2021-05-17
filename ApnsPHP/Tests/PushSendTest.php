<?php

/**
 * This file contains the PushSendTest class.
 *
 * @package ApnsPHP
 * @author  Martijn van Berkum <m.vanberkum@m2mobi.com>
 */

namespace ApnsPHP\Tests;

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
    public function testSendThrowsExceptionOnNoConnection()
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
    public function testSendThrowsExceptionOnEmptyQueue()
    {
        $this->set_reflection_property_value('hSocket', true);

        $this->expectException('ApnsPHP\Push\Exception');
        $this->expectExceptionMessage('No notifications queued to be sent');

        $this->class->send();
    }

    /**
     * Test that send() doesn't retry sending a message if it has an unrecoverable error
     *
     * @covers \ApnsPHP\Push::send
     */
    public function testSendFailsWithoutRetrying()
    {
        // because we cannot mock private functions we need to mock a function that httpSend uses
        // to get it to return the result we need
        $this->mock_function('curl_exec', function () {
            return false;
        });

        $message = [ 1 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ] ];

        $this->set_reflection_property_value('environment', 1);
        $this->set_reflection_property_value('protocol', 1);
        $this->set_reflection_property_value('hSocket', curl_init());
        $this->set_reflection_property_value('messageQueue', $message);

        $this->class->setWriteInterval(0);

        $sendMessages = [
            ['Sending messages queue, run #1: 1 message(s) left in queue.'],
            ['Sending messages queue, run #2: 1 message(s) left in queue.']
        ];

        $this->class->expects($this->exactly(4))
                    ->method('logger')
                    ->will($this->returnValue($this->logger));

        $this->logger->expects($this->exactly(2))
                     ->method('info')
                     ->withConsecutive(...$sendMessages);

        $this->logger->expects($this->once())
                     ->method('debug')
                     ->with('Sending message ID 1 [custom identifier: unset] (1/3): 0 bytes.');

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with('Message ID 1 [custom identifier: unset] has an unrecoverable error
                                 (4), removing from queue without retrying...');

        $this->class->expects($this->exactly(1))
                    ->method('updateQueue')
                    ->will($this->returnCallback(function () {
                        $queue = $this->get_accessible_reflection_property('messageQueue')->getValue($this->class);
                        $queue[1]['ERRORS'][] = [
                            'command' => 8,
                            'statusCode' => 4,
                            'identifier' => 1,
                            'time' => 1620029695,
                            'statusMessage' => 'Missing payload'
                        ];
                        return true;
                    }));

        $this->class->send();

        $this->unmock_function('curl_exec');
    }

    /**
     * Test that send() retries sending a message if it fails
     *
     * @covers \ApnsPHP\Push::send
     */
    public function testSendFailsWithRetrying()
    {
        // because we cannot mock private functions we need to mock a function that httpSend uses
        // to get it to return the result we need
        $this->mock_function('curl_exec', function () {
            return false;
        });

        $message = [ 1 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ] ];

        $this->set_reflection_property_value('environment', 1);
        $this->set_reflection_property_value('protocol', 1);
        $this->set_reflection_property_value('hSocket', curl_init());
        $this->set_reflection_property_value('messageQueue', $message);

        $this->class->setWriteInterval(0);

        $queueMessages = [
            ['Sending messages queue, run #1: 1 message(s) left in queue.'],
            ['Sending messages queue, run #2: 1 message(s) left in queue.'],
            ['Sending messages queue, run #3: 1 message(s) left in queue.'],
            ['Sending messages queue, run #4: 1 message(s) left in queue.']
        ];

        $sendMessages = [
            ['Sending message ID 1 [custom identifier: unset] (1/3): 0 bytes.'],
            ['Sending message ID 1 [custom identifier: unset] (2/3): 0 bytes.'],
            ['Sending message ID 1 [custom identifier: unset] (3/3): 0 bytes.']
        ];

        $this->class->expects($this->exactly(8))
                    ->method('logger')
                    ->will($this->returnValue($this->logger));

        $this->logger->expects($this->exactly(4))
                     ->method('info')
                     ->withConsecutive(...$queueMessages);

        $this->logger->expects($this->exactly(3))
                     ->method('debug')
                     ->withConsecutive(...$sendMessages);

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with('Message ID 1 [custom identifier: unset] has 3 errors, removing from queue...');

        $this->class->expects($this->exactly(3))
                    ->method('updateQueue')
                    ->will($this->returnCallback(function () {
                        $queue = $this->get_accessible_reflection_property('messageQueue')->getValue($this->class);
                        $queue[1]['ERRORS'][] = [
                            'command' => 8,
                            'statusCode' => 999,
                            'identifier' => 1,
                            'time' => 1620029695,
                            'statusMessage' => 'Internal error'
                        ];
                        return true;
                    }));

        $this->class->send();

        $this->unmock_function('curl_exec');
    }

    /**
     * Test that send() retries sending a message if it fails
     *
     * @covers \ApnsPHP\Push::send
     */
    public function testSendRemovesWhenNoError()
    {
        // because we cannot mock private functions we need to mock a function that httpSend uses
        // to get it to return the result we need
        $this->mock_function('curl_exec', function () {
            return false;
        });

        $message = [ 1 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ] ];

        $this->set_reflection_property_value('environment', 1);
        $this->set_reflection_property_value('protocol', 1);
        $this->set_reflection_property_value('hSocket', curl_init());
        $this->set_reflection_property_value('messageQueue', $message);

        $this->class->setWriteInterval(0);

        $queueMessages = [
            ['Sending messages queue, run #1: 1 message(s) left in queue.'],
            ['Sending messages queue, run #2: 1 message(s) left in queue.'],
            ['Message ID 1 [custom identifier: unset] has no error (0),
                                 removing from queue...'],
        ];

        $this->class->expects($this->exactly(4))
                    ->method('logger')
                    ->will($this->returnValue($this->logger));

        $this->logger->expects($this->exactly(3))
                     ->method('info')
                     ->withConsecutive(...$queueMessages);

        $this->logger->expects($this->once())
                     ->method('debug')
                     ->with('Sending message ID 1 [custom identifier: unset] (1/3): 0 bytes.');

        $this->class->expects($this->exactly(1))
                    ->method('updateQueue')
                    ->will($this->returnCallback(function () {
                        $queue = $this->get_accessible_reflection_property('messageQueue')->getValue($this->class);
                        $queue[1]['ERRORS'][] = [
                            'command' => 8,
                            'statusCode' => 0,
                            'identifier' => 1,
                            'time' => 1620029695,
                            'statusMessage' => 'No error'
                        ];
                        return true;
                    }));

        $this->class->send();

        $this->unmock_function('curl_exec');
    }

    /**
     * Test that send() Successfully sends a message
     *
     * @covers \ApnsPHP\Push::send
     */
    public function testSendSuccessfullySends()
    {
        // because we cannot mock private functions we need to mock a function that httpSend uses
        // to get it to return the result we need
        $this->mock_function('curl_exec', function () {
            return true;
        });

        $message = [ 1 => [ 'MESSAGE' => $this->message, 'ERRORS' => [] ] ];

        $this->set_reflection_property_value('environment', 1);
        $this->set_reflection_property_value('protocol', 1);
        $this->set_reflection_property_value('hSocket', curl_init());
        $this->set_reflection_property_value('messageQueue', $message);

        $this->class->setWriteInterval(0);

        $this->class->expects($this->exactly(2))
                    ->method('logger')
                    ->will($this->returnValue($this->logger));

        $this->logger->expects($this->once())
                     ->method('info')
                     ->with('Sending messages queue, run #1: 1 message(s) left in queue.');

        $this->logger->expects($this->once())
                     ->method('debug')
                     ->with('Sending message ID 1 [custom identifier: unset] (1/3): 0 bytes.');

        $this->class->expects($this->once())
                    ->method('updateQueue')
                    ->will($this->returnValue(false));

        $this->class->send();

        $this->unmock_function('curl_exec');
    }
}
