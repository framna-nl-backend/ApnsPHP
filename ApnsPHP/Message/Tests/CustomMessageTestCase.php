<?php

/**
 * This file contains the CustomMessageTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2021 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace ApnsPHP\Message\Tests;

use ApnsPHP\Message\CustomMessage;
use Lunr\Halo\LunrBaseTestCase;
use ReflectionClass;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the CustomMessage class.
 *
 * @covers \ApnsPHP\Message\CustomMessage
 */
abstract class CustomMessageTestCase extends LunrBaseTestCase
{
    /**
     * Class to test
     * @var CustomMessage
     */
    protected CustomMessage $class;

    /**
     * TestCase constructor
     */
    public function setUp(): void
    {
        $this->class      = new CustomMessage();
        $this->baseSetUp($this->class);
    }

    /**
     * TestCase destructor
     */
    public function tearDown(): void
    {
        unset($this->class);
        parent::tearDown();
    }
}
