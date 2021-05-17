<?php

/**
 * This file contains the PushTest class.
 *
 * @package ApnsPHP
 * @author  Martijn van Berkum <m.vanberkum@m2mobi.com>
 */

namespace ApnsPHP\Tests;

use Lunr\Halo\LunrBaseTest;
use ReflectionClass;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the Push class.
 *
 * @covers \ApnsPHP\Push
 */
abstract class PushTest extends LunrBaseTest
{

    /**
     * Mock instance of the EmbeddedLogger class.
     * @var \ApnsPHP\Log\EmbeddedLogger
     */
    protected $logger;

    /**
     * Mock instance of the Message class.
     * @var \ApnsPHP\Message
     */
    protected $message;

    /**
     * TestCase constructor
     */
    public function setUp(): void
    {
        $this->logger = $this->getMockBuilder('ApnsPHP\Log\EmbeddedLogger')->getMock();

        $this->message = $this->getMockBuilder('ApnsPHP\Message')
                              ->disableOriginalConstructor()
                              ->getMock();

        $methods = [
            'logger',
            'updateQueue',
            'readErrorMessage',
            'disconnect',
            'connect',
            'parseErrorMessage'
        ];

        $this->class = $this->getMockBuilder('ApnsPHP\Push')
                            ->disableOriginalConstructor()
                            ->onlyMethods($methods)
                            ->getMock();

        $this->reflection = new ReflectionClass('ApnsPHP\Push');
    }

    /**
     * TestCase destructor
     */
    public function tearDown(): void
    {
        unset($this->class);
        unset($this->reflection);
        unset($this->logger);
        unset($this->message);
    }
}
