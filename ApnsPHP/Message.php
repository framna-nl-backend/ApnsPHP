<?php

/**
 * SPDX-FileCopyrightText: Copyright 2010 Aldo Armiento (aldo.armiento@gmail.com)
 * SPDX-FileCopyrightText: Copyright 2021 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace ApnsPHP;

use ApnsPHP\Message\Exception;
use ApnsPHP\Message\Priority;
use ApnsPHP\Message\PushType;

/**
 * The Push Notification Message.
 *
 * The class represents a message to be delivered to an end user device.
 * Notification Service.
 *
 * @see http://tinyurl.com/ApplePushNotificationPayload
 */
class Message
{
    /**
     * The maximum size allowed for a notification payload.
     * @var int
     */
    protected const PAYLOAD_MAXIMUM_SIZE = 4096;

    /**
     * The Apple-reserved aps namespace.
     * @var string
     */
    protected const APPLE_RESERVED_NAMESPACE = 'aps';

    /**
     * If the JSON payload is longer than maximum allowed size, shorts message text.
     * @var bool|null
     */
    protected ?bool $autoAdjustLongPayload = true;

    /**
     * Recipients device tokens.
     * @var string[]
     */
    protected array $deviceTokens = [];

    /**
     * Alert message to display to the user.
     * @var string
     */
    protected string $text;

    /**
     * Alert title to display to the user.
     * @var string
     */
    protected string $title;

    /**
     * Number to badge the application icon with.
     * @var int
     */
    protected int $badge;

    /**
     * Sound to play.
     * @var string
     */
    protected string $sound;

    /**
     * Notification category.
     * @var string
     */
    protected string $category;

    /**
     * True to initiates the Newsstand background download.
     * @see http://tinyurl.com/ApplePushNotificationNewsstand
     * @var bool|null
     */
    protected ?bool $contentAvailable;

    /**
     * True to activate mutable content key support for ios10 rich notifications.
     * @see https://developer.apple.com/reference/usernotifications/unnotificationserviceextension
     * @var bool|null
     */
    protected ?bool $mutableContent;

    /**
     * Notification thread-id.
     * @var string
     */
    protected string $threadId;

    /**
     * Custom properties container.
     * @var array<string,mixed>
     */
    protected array $customProperties = [];

    /**
     * That message will expire in 604800 seconds (86400 * 7, 7 days) if not successful delivered.
     * @var int
     */
    protected int $expiryValue = 604800;

    /**
     * Custom message identifier.
     * @var string
     */
    protected string $customIdentifier = '';

    /**
     * The topic of the remote notification, which is typically the bundle ID for your app.
     * @var string
     */
    protected string $topic = '';

    /**
     * The collapse ID of the remote notification.
     * @var string
     */
    protected string $collapseId = '';

    /**
     * The priority of the remote notification.
     * @var Priority|null
     */
    protected ?Priority $priority = null;

    /**
     * Push type
     * @var PushType|null
     */
    private ?PushType $pushType = null;

    /**
     * Constructor.
     *
     * @param string $deviceToken Recipients device token (optional).
     */
    public function __construct(?string $deviceToken = null)
    {
        if (isset($deviceToken)) {
            $this->addRecipient($deviceToken);
        }
    }

    /**
     * Add a recipient device token.
     *
     * @param string $deviceToken Recipients device token.
     */
    public function addRecipient(string $deviceToken): void
    {
        if (!preg_match('~^[a-f0-9]{64,}$~i', $deviceToken)) {
            throw new Exception(
                "Invalid device token '{$deviceToken}'"
            );
        }
        $this->deviceTokens[] = $deviceToken;
    }

    /**
     * Get a recipient.
     *
     * @param int $recipient Recipient number to return (optional).
     *
     * @return string The recipient token at index $recipient.
     */
    public function getRecipient(int $recipient = 0): string
    {
        if (!isset($this->deviceTokens[$recipient])) {
            throw new Exception(
                "No recipient at index '{$recipient}'"
            );
        }
        return $this->deviceTokens[$recipient];
    }

    /**
     * Get an object for a single recipient.
     *
     * @param int $recipient Recipient number to return (optional).
     *
     * @return Message The message configured with the token at index $recipient.
     */
    public function selfForRecipient(int $recipient = 0)
    {
        if (!isset($this->deviceTokens[$recipient])) {
            throw new Exception(
                "No recipient at index '{$recipient}'"
            );
        }

        //TODO: Replace this with actually looping over recipients
        $copy = clone $this;
        $copy->deviceTokens = [$this->deviceTokens[$recipient]];

        return $copy;
    }

    /**
     * Get the number of recipients.
     *
     * @return int Recipient's number.
     */
    public function getRecipientsCount(): int
    {
        return count($this->deviceTokens);
    }

    /**
     * Get all recipients.
     *
     * @return string[] Array of all recipients device token.
     */
    public function getRecipients(): array
    {
        return $this->deviceTokens;
    }

    /**
     * Set the alert message to display to the user.
     *
     * @param string $text An alert message to display to the user.
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * Get the alert message to display to the user.
     *
     * @return string The alert message to display to the user.
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Set the alert title to display to the user.  This will be BOLD text on the top of the push message. If
     * this title is not set - only the text will be used in the alert without bold text.
     *
     * @param string $title An alert title to display to the user.
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Get the alert title to display to the user.
     *
     * @return string The alert title to display to the user.
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set the number to badge the application icon with.
     *
     * @param int $badge A number to badge the application icon with.
     */
    public function setBadge(int $badge): void
    {
        $this->badge = $badge;
    }

    /**
     * Get the number to badge the application icon with.
     *
     * @return int The number to badge the application icon with.
     */
    public function getBadge(): int
    {
        return $this->badge;
    }

    /**
     * Set the sound to play.
     *
     * @param string $sound A sound to play ('default' is the default sound).
     */
    public function setSound(string $sound = 'default'): void
    {
        $this->sound = $sound;
    }

    /**
     * Get the sound to play.
     *
     * @return string The sound to play.
     */
    public function getSound(): string
    {
        return $this->sound;
    }

    /**
     * Set the category of notification
     *
     * @param string $category A category for ios8 notification actions (optional).
     */
    public function setCategory(string $category = ''): void
    {
        $this->category = $category;
    }

    /**
     * Get the category of notification
     *
     * @return string The notification category
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
    * Set the thread-id of notification
    *
    * @param string $threadId A thread-id for iOS 12 notification group (optional).
    */
    public function setThreadId(string $threadId = ''): void
    {
        $this->threadId = $threadId;
    }

    /**
    * Get the thread-id of notification
    *
    * @return string The notification thread-id
    */
    public function getThreadId(): string
    {
        return $this->threadId;
    }

    /**
     * Initiates the Newsstand background download.
     *
     * @see http://tinyurl.com/ApplePushNotificationNewsstand
     *
     * @param bool $contentAvailable True to initiates the Newsstand background download.
     */
    public function setContentAvailable(bool $contentAvailable = true): void
    {
        $this->contentAvailable = $contentAvailable ? true : null;
    }

    /**
     * Get if should initiates the Newsstand background download.
     *
     * @return bool Initiates the Newsstand background download property.
     */
    public function getContentAvailable(): bool
    {
        return $this->contentAvailable;
    }

    /**
     * Set the mutable-content key for Notification Service Extensions on iOS10
     *
     * @see https://developer.apple.com/reference/usernotifications/unnotificationserviceextension
     *
     * @param bool $mutableContent True to enable flag
     */
    public function setMutableContent(bool $mutableContent = true): void
    {
        $this->mutableContent = $mutableContent ? true : null;
    }

    /**
     * Get if should set the mutable-content ios10 rich notifications flag
     *
     * @return bool mutable-content ios10 rich notifications flag
     */
    public function getMutableContent(): bool
    {
        return $this->mutableContent;
    }

    /**
     * Set a custom property.
     *
     * @param string $name  Custom property name.
     * @param mixed  $value Custom property value.
     */
    public function setCustomProperty(string $name, $value): void
    {
        $name = trim($name);
        if ($name == self::APPLE_RESERVED_NAMESPACE) {
            throw new Exception(
                "Property name '" . self::APPLE_RESERVED_NAMESPACE . "' can not be used for custom property."
            );
        }
        $this->customProperties[$name] = $value;
    }

    /**
     * Get all custom properties names.
     *
     * @return string[] All properties names.
     */
    public function getCustomPropertyNames(): array
    {
        return array_keys($this->customProperties);
    }

    /**
     * Get the custom property value.
     *
     * @param string $name Custom property name.
     *
     * @return string The custom property value.
     */
    public function getCustomProperty(string $name)
    {
        if (!array_key_exists($name, $this->customProperties)) {
            throw new Exception(
                "No property exists with the specified name '{$name}'."
            );
        }
        return $this->customProperties[$name];
    }

    /**
     * Set the auto-adjust long payload value.
     *
     * @param bool $autoAdjust If true a long payload is shorted cutting long text value.
     */
    public function setAutoAdjustLongPayload(bool $autoAdjust): void
    {
        $this->autoAdjustLongPayload = $autoAdjust;
    }

    /**
     * Get the auto-adjust long payload value.
     *
     * @return bool The auto-adjust long payload value.
     */
    public function getAutoAdjustLongPayload(): bool
    {
        return $this->autoAdjustLongPayload;
    }

    /**
     * PHP Magic Method. When an object is "converted" to a string, JSON-encoded
     * payload is returned.
     *
     * @return string JSON-encoded payload.
     */
    public function __toString(): string
    {
        try {
            $JSONPayload = $this->getPayload();
        } catch (Exception $e) {
            $JSONPayload = '';
        }
        return $JSONPayload;
    }

    /**
     * Get the payload dictionary.
     *
     * For more information on push titles see:
     * https://stackoverflow.com/questions/40647061/bold-or-other-formatting-in-ios-push-notification
     *
     * @return array<string,mixed> The payload dictionary.
     */
    protected function getPayloadDictionary(): array
    {
        $payload[self::APPLE_RESERVED_NAMESPACE] = [];

        if (isset($this->text)) {
            if (isset($this->title) && strlen($this->title) > 0) {
                // if the title is set, use it
                $payload[self::APPLE_RESERVED_NAMESPACE]['alert'] = [];
                $payload[self::APPLE_RESERVED_NAMESPACE]['alert']['title'] =  (string)$this->title;
                $payload[self::APPLE_RESERVED_NAMESPACE]['alert']['body'] = (string)$this->text;
            } else {
                // if the title is not set, use the standard alert message format
                $payload[self::APPLE_RESERVED_NAMESPACE]['alert'] = (string)$this->text;
            }
        }

        if (isset($this->badge) && $this->badge >= 0) {
            $payload[self::APPLE_RESERVED_NAMESPACE]['badge'] = (int)$this->badge;
        }
        if (isset($this->sound)) {
            $payload[self::APPLE_RESERVED_NAMESPACE]['sound'] = (string)$this->sound;
        }
        if (isset($this->contentAvailable)) {
            $payload[self::APPLE_RESERVED_NAMESPACE]['content-available'] = (int)$this->contentAvailable;
        }
        if (isset($this->mutableContent)) {
            $payload[self::APPLE_RESERVED_NAMESPACE]['mutable-content'] = (int)$this->mutableContent;
        }
        if (isset($this->category)) {
            $payload[self::APPLE_RESERVED_NAMESPACE]['category'] = (string)$this->category;
        }
        if (isset($this->threadId)) {
            $payload[self::APPLE_RESERVED_NAMESPACE]['thread-id'] = (string)$this->threadId;
        }

        foreach ($this->customProperties as $propertyName => $propertyValue) {
            $payload[$propertyName] = $propertyValue;
        }

        return $payload;
    }

    /**
     * Convert the message in a JSON-encoded payload.
     *
     * @return string JSON-encoded payload.
     */
    public function getPayload(): string
    {
        $JSON = json_encode(
            $this->getPayloadDictionary(),
            JSON_UNESCAPED_UNICODE
        );

        $JSONPayload = str_replace(
            '"' . self::APPLE_RESERVED_NAMESPACE . '":[]',
            '"' . self::APPLE_RESERVED_NAMESPACE . '":{}',
            $JSON
        );
        $JSONPayloadLength = strlen($JSONPayload);

        if ($JSONPayloadLength > self::PAYLOAD_MAXIMUM_SIZE) {
            if ($this->autoAdjustLongPayload) {
                $maxTextLength = $nTextLen = strlen($this->text) - ($JSONPayloadLength - self::PAYLOAD_MAXIMUM_SIZE);
                if ($maxTextLength > 0) {
                    while (strlen($this->text = mb_substr($this->text, 0, --$nTextLen, 'UTF-8')) > $maxTextLength);
                    return $this->getPayload();
                } else {
                    throw new Exception(
                        "JSON Payload is too long: {$JSONPayloadLength} bytes. Maximum size is " .
                        self::PAYLOAD_MAXIMUM_SIZE . " bytes. The message text can not be auto-adjusted."
                    );
                }
            } else {
                throw new Exception(
                    "JSON Payload is too long: {$JSONPayloadLength} bytes. Maximum size is " .
                    self::PAYLOAD_MAXIMUM_SIZE . " bytes"
                );
            }
        }

        return $JSONPayload;
    }

    /**
     * Set the expiry value.
     *
     * @param int $expiryValue This message will expire in N seconds if not successful delivered.
     */
    public function setExpiry(int $expiryValue): void
    {
        $this->expiryValue = $expiryValue;
    }

    /**
     * Get the expiry value.
     *
     * @return int The expire message value (in seconds).
     */
    public function getExpiry(): int
    {
        return $this->expiryValue;
    }

    /**
     * Set the custom message identifier.
     *
     * The custom message identifier is useful to associate a push notification
     * to a DB record or an User entry for example. The custom message identifier
     * can be retrieved in case of error using the getCustomIdentifier()
     * method of an entry retrieved by the getErrors() method.
     * This custom identifier, if present, is also used in all status message by
     * the ApnsPHPPush class.
     *
     * @param string $customIdentifier The custom message identifier.
     */
    public function setCustomIdentifier(string $customIdentifier): void
    {
        if (!preg_match('~[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}~i', $customIdentifier)) {
            throw new Exception('Identifier must be a UUID');
        }
        $this->customIdentifier = $customIdentifier;
    }

    /**
     * Get the custom message identifier.
     *
     * @return string The custom message identifier.
     */
    public function getCustomIdentifier(): string
    {
        return $this->customIdentifier;
    }

    /**
     * Set the topic of the remote notification, which is typically
     * the bundle ID for your app.
     *
     * @param string $topic The topic of the remote notification.
     */
    public function setTopic(string $topic): void
    {
        $this->topic = $topic;
    }

    /**
     * Get the topic of the remote notification.
     *
     * @return string The topic of the remote notification.
     */
    public function getTopic(): string
    {
        return $this->topic;
    }

    /**
     * Set the priority of the remote notification.
     *
     * @param Priority $priority The priority of the remote notification.
     */
    public function setPriority(Priority $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * Get the priority of the remote notification.
     *
     * @return Priority|null The priority of the remote notification.
     */
    public function getPriority(): ?Priority
    {
        return $this->priority;
    }

    /**
     * Set the collapse ID of the remote notification, notifications with the same collapse ID will show as one.
     *
     * @param string $collapseId The collapse ID of the remote notification.
     */
    public function setCollapseId(string $collapseId): void
    {
        $this->collapseId = $collapseId;
    }

    /**
     * Get the collapse ID of the remote notification.
     *
     * @return string The collapse ID of the remote notification.
     */
    public function getCollapseId(): string
    {
        return $this->collapseId;
    }

    /**
     * Set the push type.
     *
     * @param PushType $pushType
     */
    public function setPushType(PushType $pushType): void
    {
        $this->pushType = $pushType;
    }

    /**
     * Get the push type.
     *
     * @return PushType|null Push type
     */
    public function getPushType(): ?PushType
    {
        return $this->pushType;
    }
}
