<?php

/**
 * This file contains the SharedConfigTest class.
 *
 * @package ApnsPHP
 * @author  Martijn van Berkum <m.vanberkum@m2mobi.com>
 */

namespace ApnsPHP\Tests;

use ApnsPHP\SharedConfig;
use Lunr\Halo\LunrBaseTest;
use ReflectionClass;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the SharedConfig class.
 *
 * @covers \ApnsPHP\SharedConfig
 */
abstract class SharedConfigTest extends LunrBaseTest
{

    /**
     * Mock instance of the EmbeddedLogger class.
     * @var \ApnsPHP\Log\EmbeddedLogger
     */
    protected $logger;

    /**
     * TestCase constructor
     */
    public function setUp(): void
    {
        $constructorArgs = [
            SharedConfig::ENVIRONMENT_SANDBOX,
            'ApnsPHP/Tests/SharedConfigTest.php',
            SharedConfig::PROTOCOL_HTTP
        ];

        $this->logger = $this->getMockBuilder('ApnsPHP\Log\EmbeddedLogger')->getMock();

        $this->class = $this->getMockBuilder('ApnsPHP\SharedConfig')
                            ->setConstructorArgs($constructorArgs)
                            ->onlyMethods([ 'httpInit', 'getJsonWebToken', 'logger' ])
                            ->getMockForAbstractClass();

        $this->reflection = new ReflectionClass('ApnsPHP\SharedConfig');
    }

    /**
     * TestCase destructor
     */
    public function tearDown(): void
    {
        unset($this->class);
        unset($this->reflection);
        unset($this->logger);
    }
}
