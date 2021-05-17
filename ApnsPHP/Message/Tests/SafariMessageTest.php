<?php

/**
 * This file contains the SafariMessageTest class.
 *
 * @package ApnsPHP
 * @author  Martijn van Berkum <m.vanberkum@m2mobi.com>
 */

namespace ApnsPHP\Message\Tests;

use ApnsPHP\Message\SafariMessage;
use Lunr\Halo\LunrBaseTest;
use ReflectionClass;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the SafariMessage class.
 *
 * @covers \ApnsPHP\Message\SafariMessage
 */
abstract class SafariMessageTest extends LunrBaseTest
{

    /**
     * TestCase constructor
     */
    public function setUp(): void
    {
        $this->reflection = new ReflectionClass('ApnsPHP\Message\SafariMessage');
        $this->class      = new SafariMessage();
    }

    /**
     * TestCase destructor
     */
    public function tearDown(): void
    {
        unset($this->class);
        unset($this->reflection);
    }
}
