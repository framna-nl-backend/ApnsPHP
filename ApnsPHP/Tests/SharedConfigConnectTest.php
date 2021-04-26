<?php

/**
 * This file contains the SharedConfigConnectTest class.
 *
 * @package ApnsPHP
 * @author  Martijn van Berkum <m.vanberkum@m2mobi.com>
 */

namespace ApnsPHP\Tests;

use ApnsPHP\Exception;

/**
 * This class contains tests for the connect function
 *
 * @covers \ApnsPHP\SharedConfig
 */
class SharedConfigConnectTest extends SharedConfigTest
{

    /**
     * Test that connect() connects successfully
     *
     * @covers \ApnsPHP\SharedConfig::connect
     */
    public function testConnectSuccess()
    {
        $this->class->expects($this->once())
                    ->method('httpInit')
                    ->will($this->returnValue(true));

        $this->class->connect();
    }

    /**
     * Test that connect() throws an exception when failing to connect
     *
     * @covers \ApnsPHP\SharedConfig::connect
     */
    public function testConnectThrowsExceptionOnHttpInitFail()
    {
        $this->set_reflection_property_value('connectRetryInterval', 0);

        $this->class->expects($this->exactly(4))
                    ->method('httpInit')
                    ->will($this->throwException(new Exception('Unable to initialize HTTP/2 backend.')));

        $message = [
            ['Retry to connect (1/3)...'],
            ['Retry to connect (2/3)...'],
            ['Retry to connect (3/3)...']
        ];

        $this->class->expects($this->exactly(7))
                    ->method('logger')
                    ->will($this->returnValue($this->logger));

        $this->logger->expects($this->exactly(4))
                     ->method('error')
                     ->with('Unable to initialize HTTP/2 backend.');

        $this->logger->expects($this->exactly(3))
                     ->method('info')
                     ->withConsecutive(...$message);

        $this->expectException('ApnsPHP\Exception');
        $this->expectExceptionMessage('Unable to initialize HTTP/2 backend.');

        $this->class->connect();
    }
}
