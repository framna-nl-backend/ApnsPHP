<?php

/**
 * This file contains the LiveActivity class.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace ApnsPHP\Message;

use ApnsPHP\Message;
use RuntimeException;
use UnexpectedValueException;

/**
 * Live activity style message.
 */
class LiveActivity extends Message
{
    /**
     * Notification activity-id.
     * @var string
     */
    protected string $activityId;

    /**
     * Content state information
     * @var array<string,mixed>|object
     */
    private array|object $state;

    /**
     * Attributes at the start of an activity
     * @var array<string,mixed>|object
     */
    private array|object $attributes;

    /**
     * Type of attributes
     * @var string
     */
    private string $attributes_type;

    /**
     * Event for the activity
     * @var LiveActivityEvent
     */
    private LiveActivityEvent $event;

    /**
     * Timestamp when the activity will become stale
     * @var int
     */
    private int $stale_timestamp;

    /**
     * Timestamp when the activity will dismiss itself
     * @var int
     */
    private int $dismiss_timestamp;

    /**
     * Constructor.
     *
     * @param string|null $deviceToken Recipients device token (optional).
     */
    public function __construct(?string $deviceToken = null)
    {
        parent::__construct($deviceToken);
        parent::setPushType(PushType::LiveActivity);
    }

    /**
     * Get the payload dictionary.
     *
     * @return array<string,mixed> The payload dictionary.
     */
    public function getPayloadDictionary(): array
    {
        $payload = parent::getPayloadDictionary();

        $payload[self::APPLE_RESERVED_NAMESPACE]['event'] = $this->event->value;
        $payload[self::APPLE_RESERVED_NAMESPACE]['timestamp'] = time();
        if (isset($this->state)) {
            $payload[self::APPLE_RESERVED_NAMESPACE]['content-state'] = $this->state;
        }
        if (isset($this->stale_timestamp)) {
            $payload[self::APPLE_RESERVED_NAMESPACE]['stale-date'] = $this->stale_timestamp;
        }
        if (isset($this->dismiss_timestamp)) {
            $payload[self::APPLE_RESERVED_NAMESPACE]['dismissal-date'] = $this->dismiss_timestamp;
        }
        if (isset($this->activityId)) {
            $payload[self::APPLE_RESERVED_NAMESPACE]['activity-id'] = $this->activityId;
        }

        if ($this->event !== LiveActivityEvent::Start) {
            return $payload;
        }

        if (isset($this->attributes) && isset($this->attributes_type)) {
            $payload[self::APPLE_RESERVED_NAMESPACE]['attributes-type'] = $this->attributes_type;
            $payload[self::APPLE_RESERVED_NAMESPACE]['attributes'] = $this->attributes;
        }

        return $payload;
    }

    /**
     * Set a topic
     *
     * @throws UnexpectedValueException if the topic is invalid for a live activity.
     *
     * @param string $topic
     *
     * @return void
     */
    public function setTopic(string $topic): void
    {
        if (!str_contains($topic, '.push-type.liveactivity')) {
            throw new UnexpectedValueException("Topic '$topic' does not include '.push-type.liveactivity'!");
        }

        parent::setTopic($topic);
    }

    /**
     * Set a push type
     *
     * @throws RuntimeException Since the push type is tied to the class
     *
     * @param PushType $pushType
     *
     * @return void
     */
    public function setPushType(PushType $pushType): void
    {
        throw new RuntimeException('Push type is enforced by the class!');
    }

    /**
     * Set the event for the activity
     *
     * @param LiveActivityEvent $event The activity event
     */
    public function setEvent(LiveActivityEvent $event): void
    {
        $this->event = $event;
    }

    /**
     * Get the event for the activity
     * @return LiveActivityEvent
     */
    public function getEvent(): LiveActivityEvent
    {
        return $this->event;
    }

    /**
     * Set the attributes for the start of the activity
     *
     * @param array<string,mixed>|object $attributes The attributes to set
     */
    public function setAttributes(array|object $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * Get the attributes for the start activity
     *
     * @return object|array<string,mixed>
     */
    public function getAttributes(): object|array
    {
        return $this->attributes;
    }

    /**
     * Set the attribute type for the start of the activity.
     *
     * @param string $type The attribute type
     *
     * @return void
     */
    public function setAttributesType(string $type): void
    {
        $this->attributes_type = $type;
    }

    /**
     * Get the attribute type for the start of the activity.
     *
     * @return string The attribute type
     */
    public function getAttributesType(): string
    {
        return $this->attributes_type;
    }

    /**
     * Set the timestamp when the information goes stale
     *
     * @param int $stale_timestamp The timestamp at which the information goes stale
     *
     * @return void
     */
    public function setStaleTimestamp(int $stale_timestamp): void
    {
        $this->stale_timestamp = $stale_timestamp;
    }

    /**
     * Get the timestamp when the information goes stale
     *
     * @return int The timestamp at which the information goes stale
     */
    public function getStaleTimestamp(): int
    {
        return $this->stale_timestamp;
    }

    /**
     * Set the timestamp when the activity dismisses itself
     *
     * @param int $dismiss_timestamp The timestamp at which the activity dismisses
     *
     * @return void
     */
    public function setDismissTimestamp(int $dismiss_timestamp): void
    {
        $this->dismiss_timestamp = $dismiss_timestamp;
    }

    /**
     * Get the timestamp when the activity dismisses itself
     *
     * @return int The timestamp at which the activity dismisses
     */
    public function getDismissTimestamp(): int
    {
        return $this->dismiss_timestamp;
    }

    /**
     * Set the content state
     *
     * @param array<string,mixed>|object $state The content state to relay to the app
     *
     * @return void
     */
    public function setContentState(array|object $state): void
    {
        $this->state = $state;
    }

    /**
     * Get the content state
     *
     * @return array<string,mixed>|object The content state that should be relayed to the app
     */
    public function getContentState(): object|array
    {
        return $this->state;
    }

    /**
     * Set the activity-id of a notification
     *
     * @param string $activityId An activity-id (undocumented).
     */
    public function setActivityId(string $activityId): void
    {
        $this->activityId = $activityId;
    }

    /**
     * Get the activity-id of a notification
     *
     * @return string An activity-id (undocumented).
     */
    public function getActivityId(): string
    {
        return $this->activityId;
    }
}
