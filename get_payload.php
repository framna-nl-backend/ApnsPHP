<?php

/**
 * Get the payload
 *
 * phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols, PSR1.Classes.ClassDeclaration.MissingNamespace
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: BSD-3-Clause
 */

// Using Composer autoload all classes are loaded on-demand
require_once 'vendor/autoload.php';

$type = $argv[1] ?? 'message';

// Instantiate a new Message with a single recipient
$message = match ($type) {
    'message' => new \ApnsPHP\Message(),
};

// Set a custom identifier. To get back this identifier use the getCustomIdentifier() method
// over a ApnsPHP_Message object retrieved with the getErrors() message.
$message->setCustomIdentifier('7530A828-E58E-433E-A38F-D8042208CF96');

// Set badge icon to "3"
$message->setBadge(3);

// Set a simple welcome text
$message->setText('Hello APNs-enabled device!');

// Play the default sound
$message->setSound();

// Set a custom property
$message->setCustomProperty('acme2', ['bang', 'whiz']);

// Set another custom property
$message->setCustomProperty('acme3', ['bing', 'bong']);

// Set the expiry value to 30 seconds
$message->setExpiry(30);

echo $message->getPayload();
