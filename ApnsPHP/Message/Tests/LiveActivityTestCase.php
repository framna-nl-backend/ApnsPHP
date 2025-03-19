<?php

/**
 * This file contains the LiveActivityTestBase class.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace ApnsPHP\Message\Tests;

use ApnsPHP\Message\LiveActivity;
use Lunr\Halo\LunrBaseTestCase;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the Message class.
 *
 * @covers \ApnsPHP\Message\LiveActivity
 */
abstract class LiveActivityTestCase extends LunrBaseTestCase
{
    /**
     * Class to test
     * @var LiveActivity
     */
    protected LiveActivity $class;

    /**
     * TestCase constructor
     */
    public function setUp(): void
    {
        $this->class = new LiveActivity();
        $this->baseSetUp($this->class);
    }

    /**
     * TestCase destructor
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->class);
    }
}
