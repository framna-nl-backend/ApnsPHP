<?php

namespace ApnsPHP\Message;

use ApnsPHP\Message;

/**
 * The SafariMessage Push Notification Message.
 *
 * The class represents a SafariMessage Push Notification message.
 */
class SafariMessage extends Message
{
    /**< @type string The title of the notification. */
    protected $title;

    /**< @type string The label of the action button, if the user sets the notifications to appear as alerts. */
    protected $action;

    /**< @type array Variable string values to appear in place of the format specifiers in urlFormatString. */
    protected $urlArgs;

    /**
     * Set the title of the notification.
     *
     * @param  $title @type string The title of the notification
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get the title of the notification.
     *
     * @return @type string The title of the notification
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the label of the action button, if the user sets the notifications to appear as alerts.
     *
     * @param  $action @type string The label of the action button
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * Get the label of the action button, if the user sets the notifications to appear as alerts.
     *
     * @return @type string The label of the action button
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set the variable string values to appear in place of the format specifiers
     * in urlFormatString.
     *
     * @param  $urlArgs @type array The variable string values.
     */
    public function setUrlArgs($urlArgs)
    {
        $this->urlArgs = $urlArgs;
    }

    /**
     * Get the variable string values to appear in place of the format specifiers
     * in urlFormatString.
     *
     * @return @type string The variable string values.
     */
    public function getUrlArgs()
    {
        return $this->urlArgs;
    }

    /**
     * Get the payload dictionary.
     *
     * @return @type array The payload dictionary.
     */
    protected function getPayloadDictionary()
    {
        $payload[self::APPLE_RESERVED_NAMESPACE]['alert'] = array();

        if (isset($this->title)) {
            $payload[self::APPLE_RESERVED_NAMESPACE]['alert']['title'] = (string)$this->title;
        }

        if (isset($this->text)) {
            $payload[self::APPLE_RESERVED_NAMESPACE]['alert']['body'] = (string)$this->text;
        }

        if (isset($this->action)) {
            $payload[self::APPLE_RESERVED_NAMESPACE]['alert']['action'] = (string)$this->action;
        }

        if (isset($this->urlArgs)) {
            $payload[self::APPLE_RESERVED_NAMESPACE]['url-args'] = $this->urlArgs;
        }

        return $payload;
    }
}
