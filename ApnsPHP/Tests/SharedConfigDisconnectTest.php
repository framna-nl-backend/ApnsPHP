<?php

/**
 * This file contains the SharedConfigDisconnectTest class.
 *
 * @package ApnsPHP
 * @author  Martijn van Berkum <m.vanberkum@m2mobi.com>
 */

namespace ApnsPHP\Tests;

/**
 * This class contains tests for the disconnect function
 *
 * @covers \ApnsPHP\SharedConfig
 */
class SharedConfigDisconnectTest extends SharedConfigTest
{
    /**
     * Test that disconnect() disconnects successfully
     *
     * @covers \ApnsPHP\SharedConfig::disconnect
     */
    public function testDisconnectSuccess()
    {
        $this->set_reflection_property_value('hSocket', curl_init());

        $this->class->expects($this->once())
                    ->method('logger')
                    ->will($this->returnValue($this->logger));

        $this->logger->expects($this->once())
                     ->method('info')
                     ->with('Disconnected.');

        $result =  $this->class->disconnect();

        $this->assertTrue($result);
    }

    /**
     * Test that disconnect() disconnects returns false if it isn't connected to begin with
     *
     * @covers \ApnsPHP\SharedConfig::disconnect
     */
    public function testDisconnectReturnsFalse()
    {
        $result = $this->class->disconnect();

        $this->assertFalse($result);
    }
}
