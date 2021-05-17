<?php

/**
 * This file contains the MessageTest class.
 *
 * @package ApnsPHP
 * @author  Martijn van Berkum <m.vanberkum@m2mobi.com>
 */

namespace ApnsPHP\Tests;

use ApnsPHP\Message;
use Lunr\Halo\LunrBaseTest;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the Message class.
 *
 * @covers \ApnsPHP\Message
 */
abstract class MessageTest extends LunrBaseTest
{

    /**
     * TestCase constructor
     */
    public function setUp(): void
    {
        $this->reflection = new ReflectionClass('ApnsPHP\Message');
        $this->class      = new Message();
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
