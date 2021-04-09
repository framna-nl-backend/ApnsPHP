<?php

/**
 * @file
 * Message class definition.
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://code.google.com/p/apns-php/wiki/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to aldo.armiento@gmail.com so we can send you a copy immediately.
 *
 * @author (C) 2010 Aldo Armiento (aldo.armiento@gmail.com)
 * @version $Id$
 */

namespace ApnsPHP;

use ApnsPHP\Message\Exception;

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
    /**< @type integer The maximum size allowed for a notification payload. */
    protected const PAYLOAD_MAXIMUM_SIZE = 2048;

    /**< @type string The Apple-reserved aps namespace. */
    protected const APPLE_RESERVED_NAMESPACE = 'aps';

    /**< @type boolean If the JSON payload is longer than maximum allowed size, shorts message text. */
    protected $autoAdjustLongPayload = true;

    /**< @type array Recipients device tokens. */
    protected $deviceTokens = array();

    /**< @type string Alert message to display to the user. */
    protected $text;

    /**< @type string Alert title to display to the user. */
    protected $title;

    /**< @type integer Number to badge the application icon with. */
    protected $badge;

    /**< @type string Sound to play. */
    protected $sound;

    /**< @type string notification category. */
    protected $category;

    /**< @type boolean True to initiates the Newsstand background download.
     * @see http://tinyurl.com/ApplePushNotificationNewsstand */
    protected $contentAvailable;

    /**< @type boolean True to activate mutable content key support for ios10 rich notifications.
     * @see https://developer.apple.com/reference/usernotifications/unnotificationserviceextension */
    protected $mutableContent;

    /**< @type string notification thread-id. */
    protected $threadId;

    /**< @type mixed Custom properties container. */
    protected $customProperties;

    /**< @type integer That message will expire in 604800 seconds (86400 * 7, 7 days) if not successful delivered. */
    protected $expiryValue = 604800;

    /**< @type mixed Custom message identifier. */
    protected $customIdentifier;

    /**< @type string The topic of the remote notification, which is typically the bundle ID for your app. */
    protected $topic;

    /**< @type string The collapse ID of the remote notification. */
    protected $collapseId;

    /**< @type int The priority of the remote notification. */
    protected $priority;

    /**
     * Constructor.
     *
     * @param  $deviceToken @type string @optional Recipients device token.
     */
    public function __construct($deviceToken = null)
    {
        if (isset($deviceToken)) {
            $this->addRecipient($deviceToken);
        }
    }

    /**
     * Add a recipient device token.
     *
     * @param  $deviceToken @type string Recipients device token.
     */
    public function addRecipient($deviceToken)
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
     * @param  $recipient @type integer @optional Recipient number to return.
     * @return @type string The recipient token at index $recipient.
     */
    public function getRecipient($recipient = 0)
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
     * @param  $recipient @type integer @optional Recipient number to return.
     * @return Message The message configured with the token at index $recipient.
     */
    public function selfForRecipient($recipient = 0)
    {
        if (!isset($this->deviceTokens[$recipient])) {
            throw new Exception(
                "No recipient at index '{$recipient}'"
            );
        }

        //TODO: Replace this with actuall looping over recipients
        $copy = clone $this;
        $copy->deviceTokens = [$this->deviceTokens[$recipient]];

        return $copy;
    }

    /**
     * Get the number of recipients.
     *
     * @return @type integer Recipient's number.
     */
    public function getRecipientsNumber()
    {
        return count($this->deviceTokens);
    }

    /**
     * Get all recipients.
     *
     * @return @type array Array of all recipients device token.
     */
    public function getRecipients()
    {
        return $this->deviceTokens;
    }

    /**
     * Set the alert message to display to the user.
     *
     * @param  $text @type string An alert message to display to the user.
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Get the alert message to display to the user.
     *
     * @return @type string The alert message to display to the user.
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set the alert title to display to the user.  This will be BOLD text on the top of the push message. If
     * this title is not set - only the text will be used in the alert without bold text.
     *
     * @param  $title @type string An alert title to display to the user.
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get the alert title to display to the user.
     *
     * @return @type string The alert title to display to the user.
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the number to badge the application icon with.
     *
     * @param  $badge @type integer A number to badge the application icon with.
     */
    public function setBadge($badge)
    {
        if (!is_int($badge)) {
            throw new Exception(
                "Invalid badge number '{$badge}'"
            );
        }
        $this->badge = $badge;
    }

    /**
     * Get the number to badge the application icon with.
     *
     * @return @type integer The number to badge the application icon with.
     */
    public function getBadge()
    {
        return $this->badge;
    }

    /**
     * Set the sound to play.
     *
     * @param  $sound @type string @optional A sound to play ('default sound' is
     *         the default sound).
     */
    public function setSound($sound = 'default')
    {
        $this->sound = $sound;
    }

    /**
     * Get the sound to play.
     *
     * @return @type string The sound to play.
     */
    public function getSound()
    {
        return $this->sound;
    }

    /**
     * Set the category of notification
     *
     * @param  $category @type string @optional A category for ios8 notification actions.
     */
    public function setCategory($category = '')
    {
        $this->category = $category;
    }

    /**
     * Get the category of notification
     *
     * @return @type string The notification category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
    * Set the thread-id of notification
    *
    * @param  $threadId @type string @optional A thread-id for iOS 12 notification group.
    */
    public function setThreadId($threadId = '')
    {
        $this->threadId = $threadId;
    }

    /**
    * Get the thread-id of notification
    *
    * @return @type string The notification thread-id
    */
    public function getThreadId()
    {
        return $this->threadId;
    }

    /**
     * Initiates the Newsstand background download.
     * @see http://tinyurl.com/ApplePushNotificationNewsstand
     *
     * @param  $contentAvailable @type boolean True to initiates the Newsstand background download.
     */
    public function setContentAvailable($contentAvailable = true)
    {
        if (!is_bool($contentAvailable)) {
            throw new Exception(
                "Invalid content-available value '{$contentAvailable}'"
            );
        }
        $this->contentAvailable = $contentAvailable ? true : null;
    }

    /**
     * Get if should initiates the Newsstand background download.
     *
     * @return @type boolean Initiates the Newsstand background download property.
     */
    public function getContentAvailable()
    {
        return $this->contentAvailable;
    }

    /**
     * Set the mutable-content key for Notification Service Extensions on iOS10
     * @see https://developer.apple.com/reference/usernotifications/unnotificationserviceextension
     *
     * @param  $mutableContent @type boolean True to enable flag
     */
    public function setMutableContent($mutableContent = true)
    {
        if (!is_bool($mutableContent)) {
            throw new Exception(
                "Invalid mutable-content value '{$mutableContent}'"
            );
        }
        $this->mutableContent = $mutableContent ? true : null;
    }

    /**
     * Get if should set the mutable-content ios10 rich notifications flag
     *
     * @return @type boolean mutable-content ios10 rich notifications flag
     */
    public function getMutableContent()
    {
        return $this->mutableContent;
    }

    /**
     * Set a custom property.
     *
     * @param  $name @type string Custom property name.
     * @param  $value @type mixed Custom property value.
     */
    public function setCustomProperty($name, $value)
    {
        if (trim($name) == self::APPLE_RESERVED_NAMESPACE) {
            throw new Exception(
                "Property name '" . self::APPLE_RESERVED_NAMESPACE . "' can not be used for custom property."
            );
        }
        $this->customProperties[trim($name)] = $value;
    }

    /**
     * Get the first custom property name.
     *
     * @deprecated Use getCustomPropertyNames() instead.
     *
     * @return @type string The first custom property name.
     */
    public function getCustomPropertyName()
    {
        if (!is_array($this->customProperties)) {
            return;
        }
        $keys = array_keys($this->customProperties);
        return $keys[0];
    }

    /**
     * Get the first custom property value.
     *
     * @deprecated Use getCustomProperty() instead.
     *
     * @return @type mixed The first custom property value.
     */
    public function getCustomPropertyValue()
    {
        if (!is_array($this->customProperties)) {
            return;
        }
        $aKeys = array_keys($this->customProperties);
        return $this->customProperties[$aKeys[0]];
    }

    /**
     * Get all custom properties names.
     *
     * @return @type array All properties names.
     */
    public function getCustomPropertyNames()
    {
        if (!is_array($this->customProperties)) {
            return array();
        }
        return array_keys($this->customProperties);
    }

    /**
     * Get the custom property value.
     *
     * @param  $name @type string Custom property name.
     * @return @type string The custom property value.
     */
    public function getCustomProperty($name)
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
     * @param  $autoAdjust @type boolean If true a long payload is shorted cutting
     *         long text value.
     */
    public function setAutoAdjustLongPayload($autoAdjust)
    {
        $this->autoAdjustLongPayload = (bool)$autoAdjust;
    }

    /**
     * Get the auto-adjust long payload value.
     *
     * @return @type boolean The auto-adjust long payload value.
     */
    public function getAutoAdjustLongPayload()
    {
        return $this->autoAdjustLongPayload;
    }

    /**
     * PHP Magic Method. When an object is "converted" to a string, JSON-encoded
     * payload is returned.
     *
     * @return @type string JSON-encoded payload.
     */
    public function __toString()
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
     * For more information on push titles see:
     * https://stackoverflow.com/questions/40647061/bold-or-other-formatting-in-ios-push-notification
     *
     * @return @type array The payload dictionary.
     */
    protected function getPayloadDictionary()
    {
        $payload[self::APPLE_RESERVED_NAMESPACE] = array();

        if (isset($this->text)) {
            if (isset($this->title) && strlen($this->title) > 0) {
                // if the title is set, use it
                $payload[self::APPLE_RESERVED_NAMESPACE]['alert'] = array();
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

        if (is_array($this->customProperties)) {
            foreach ($this->customProperties as $propertyName => $propertyValue) {
                $payload[$propertyName] = $propertyValue;
            }
        }

        return $payload;
    }

    /**
     * Convert the message in a JSON-encoded payload.
     *
     * @return string JSON-encoded payload.
     */
    public function getPayload()
    {
        $JSON = json_encode(
            $this->getPayloadDictionary(),
            defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0
        );
        if (!defined('JSON_UNESCAPED_UNICODE') && function_exists('mb_convert_encoding')) {
            $JSON = preg_replace_callback('~\\\\u([0-9a-f]{4})~i', function ($matches) {
                return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UTF-16");
            }, $JSON);
        }

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
     * @param  $expiryValue @type integer This message will expire in N seconds
     *         if not successful delivered.
     */
    public function setExpiry($expiryValue)
    {
        if (!is_int($expiryValue)) {
            throw new Exception(
                "Invalid seconds number '{$expiryValue}'"
            );
        }
        $this->expiryValue = $expiryValue;
    }

    /**
     * Get the expiry value.
     *
     * @return @type integer The expire message value (in seconds).
     */
    public function getExpiry()
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
     * @param  $customIdentifier @type mixed The custom message identifier.
     */
    public function setCustomIdentifier($customIdentifier)
    {
        if (!preg_match('~[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}~i', $customIdentifier)) {
            throw new Exception('Identifier must be a UUID');
        }
        $this->customIdentifier = $customIdentifier;
    }

    /**
     * Get the custom message identifier.
     *
     * @return @type mixed The custom message identifier.
     */
    public function getCustomIdentifier()
    {
        return $this->customIdentifier;
    }

    /**
     * Set the topic of the remote notification, which is typically
     * the bundle ID for your app.
     *
     * @param  $topic @type string The topic of the remote notification.
     */
    public function setTopic($topic)
    {
        $this->topic = $topic;
    }

    /**
     * Get the topic of the remote notification.
     *
     * @return @type string The topic of the remote notification.
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Set the priority of the remote notification, which is 5 (low) or 10 (high).
     *
     * @param int $priority The priority of the remote notification.
     */
    public function setPriority($priority)
    {
        if (!in_array($priority, [5, 10])) {
            throw new Exception('Invalid priority');
        }

        $this->priority = $priority;
    }

    /**
     * Get the priority of the remote notification.
     *
     * @return int The priority of the remote notification.
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set the collapse ID of the remote notification, notifications with the same collapse ID will show as one.
     *
     * @param string $collapseId The collapse ID of the remote notification.
     */
    public function setCollapseId($collapseId)
    {
        $this->collapseId = $collapseId;
    }

    /**
     * Get the collapse ID of the remote notification.
     *
     * @return int The collapse ID of the remote notification.
     */
    public function getCollapseId()
    {
        return $this->collapseId;
    }
}
