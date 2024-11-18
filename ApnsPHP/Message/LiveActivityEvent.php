<?php

/**
 * This file contains the LiveActivityEvent enum.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace ApnsPHP\Message;

/**
 * Live activity events.
 */
enum LiveActivityEvent: string
{
    /**
     * Indicates this message starts the live activity
     */
    case Start = 'start';

    /**
     * Indicates this message updates the live activity
     */
    case Update = 'update';

    /**
     * Indicates this message ends the live activity
     */
    case End = 'end';
}
