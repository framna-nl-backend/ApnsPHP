<?php

/**
 * This file contains the SharedConfigHttpInitTest class.
 *
 * @package ApnsPHP
 * @author  Martijn van Berkum <m.vanberkum@m2mobi.com>
 */

namespace ApnsPHP\Tests;

use ApnsPHP\Exception;

/**
 * This class contains tests for the httpInit function
 *
 * @covers \ApnsPHP\SharedConfig
 */
class SharedConfigHttpInitTest extends SharedConfigTest
{

    /**
     * Test that httpInit() succeeds with certificate
     *
     * @covers \ApnsPHP\SharedConfig::httpInit
     */
    public function testHttpInitSucceedsWithCertificate()
    {
        $this->set_reflection_property_value('providerCertFile', 'cert.pem');

        $message = [
            ['Trying to initialize HTTP/2 backend...'],
            ['Initializing HTTP/2 backend with certificate.'],
            ['Initialized HTTP/2 backend.']
        ];

        $this->class->expects($this->exactly(3))
                    ->method('logger')
                    ->will($this->returnValue($this->logger));

        $this->logger->expects($this->exactly(3))
                     ->method('info')
                     ->withConsecutive(...$message);

        $method = $this->get_accessible_reflection_method('httpInit');
        $result = $method->invoke($this->class);

        $this->assertTrue($result);
    }

    /**
     * Test that httpInit() succeeds with key
     *
     * @covers \ApnsPHP\SharedConfig::httpInit
     */
    public function testHttpInitSucceedsWithKey()
    {
        $this->set_reflection_property_value('providerCertFile', 'key.p8');

        $message = [
            ['Trying to initialize HTTP/2 backend...'],
            ['Initializing HTTP/2 backend with key.'],
            ['Initialized HTTP/2 backend.']
        ];

        $this->class->expects($this->exactly(3))
                    ->method('logger')
                    ->will($this->returnValue($this->logger));

        $this->logger->expects($this->exactly(3))
                     ->method('info')
                     ->withConsecutive(...$message);

        $this->class->expects($this->once())
                    ->method('getJsonWebToken')
                    ->will($this->returnValue('tokenString'));

        $method = $this->get_accessible_reflection_method('httpInit');
        $result = $method->invoke($this->class);

        $this->assertTrue($result);
    }

    /**
     * Test that httpInit() throws an exception when curl_init() fails
     *
     * @covers \ApnsPHP\SharedConfig::httpInit
     */
    public function testHttpInitThrowsExceptionOnCurlInitFail()
    {
        $this->mock_function('curl_init', function () {
            return false;
        });

        $this->class->expects($this->once())
                    ->method('logger')
                    ->will($this->returnValue($this->logger));

        $this->logger->expects($this->once())
                     ->method('info')
                     ->with('Trying to initialize HTTP/2 backend...');

        $this->expectException('ApnsPHP\Exception');
        $this->expectExceptionMessage('Unable to initialize HTTP/2 backend.');

        $method = $this->get_accessible_reflection_method('httpInit');

        try {
            $method->invoke($this->class);
        } catch (Exception $e) {
            $this->unmock_function('curl_init');

            throw $e;
        }
    }

    /**
     * Test that httpInit() throws an exception when curl_setopt_array() fails
     *
     * @covers \ApnsPHP\SharedConfig::httpInit
     */
    public function testHttpInitThrowsExceptionOnCurlSetoptFail()
    {
        $this->set_reflection_property_value('providerCertFile', 'key.p8');

        $this->mock_function('curl_setopt_array', function () {
            return false;
        });

        $message = [
            ['Trying to initialize HTTP/2 backend...'],
            ['Initializing HTTP/2 backend with key.']
        ];

        $this->class->expects($this->exactly(2))
                    ->method('logger')
                    ->will($this->returnValue($this->logger));

        $this->logger->expects($this->exactly(2))
                     ->method('info')
                     ->withConsecutive(...$message);

        $this->expectException('ApnsPHP\Exception');
        $this->expectExceptionMessage('Unable to initialize HTTP/2 backend.');

        $method = $this->get_accessible_reflection_method('httpInit');

        try {
            $method->invoke($this->class);
        } catch (Exception $e) {
            $this->unmock_function('curl_setopt_array');

            throw $e;
        }
    }
}
