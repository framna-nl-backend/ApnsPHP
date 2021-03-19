<?php

/**
 * @file
 * Feedback class definition.
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

/**
 * The Feedback Service client.
 *
 * Apple Push Notification Service includes a feedback service that APNs continually
 * updates with a per-application list of devices for which there were failed-delivery
 * attempts. Providers should periodically query the feedback service to get the
 * list of device tokens for their applications, each of which is identified by
 * its topic. Then, after verifying that the application hasn’t recently been re-registered
 * on the identified devices, a provider should stop sending notifications to these
 * devices.
 *
 * @see http://tinyurl.com/ApplePushNotificationFeedback
 */
class Feedback extends SharedConfig
{
    /**< @type integer Timestamp binary size in bytes. */
    protected const TIME_BINARY_SIZE = 4;

    /**< @type integer Token length binary size in bytes. */
    protected const TOKEN_LENGTH_BINARY_SIZE = 2;

    protected $serviceURLs = array(
        'tls://feedback.push.apple.com:2196', // Production environment
        'tls://feedback.sandbox.push.apple.com:2196' // Sandbox environment
    ); /**< @type array Feedback URLs environments. */

    protected $feedback; /**< @type array Feedback container. */

    /**
     * Receives feedback tuples from Apple Push Notification Service feedback.
     *
     * Every tuple (array) contains:
     * @li @c timestamp indicating when the APNs determined that the application
     *     no longer exists on the device. This value represents the seconds since
     *     1970, anchored to UTC. You should use the timestamp to determine if the
     *     application on the device re-registered with your service since the moment
     *     the device token was recorded on the feedback service. If it hasn’t,
     *     you should cease sending push notifications to the device.
     * @li @c tokenLength The length of the device token (usually 32 bytes).
     * @li @c deviceToken The device token.
     *
     * @return @type array Array of feedback tuples (array).
     */
    public function receive()
    {
        $feedbackTupleLength = self::TIME_BINARY_SIZE + self::TOKEN_LENGTH_BINARY_SIZE + self::DEVICE_BINARY_SIZE;

        $this->feedback = array();
        $buffer = '';
        while (!feof($this->hSocket)) {
            $this->logger()->info('Reading...');
            $buffer .= $currBuffer = fread($this->hSocket, 8192);
            $currBufferLength = strlen($currBuffer);
            if ($currBufferLength > 0) {
                $this->logger()->info("{$currBufferLength} bytes read.");
            }
            unset($currBuffer, $currBufferLength);

            $bufferLength = strlen($buffer);
            if ($bufferLength >= $feedbackTupleLength) {
                $feedbackTuples = floor($bufferLength / $feedbackTupleLength);
                for ($i = 0; $i < $feedbackTuples; $i++) {
                    $feedbackTuple = substr($buffer, 0, $feedbackTupleLength);
                    $buffer = substr($buffer, $feedbackTupleLength);
                    $this->feedback[] = $feedback = $this->parseBinaryTuple($feedbackTuple);
                    $this->logger()->info(sprintf(
                        "New feedback tuple: timestamp=%d (%s), tokenLength=%d, deviceToken=%s.",
                        $feedback['timestamp'],
                        date('Y-m-d H:i:s', $feedback['timestamp']),
                        $feedback['tokenLength'],
                        $feedback['deviceToken']
                    ));
                    unset($feedback);
                }
            }

            $read = array($this->hSocket);
            $null = null;
            $changedStreams = stream_select($read, $null, $null, 0, $this->socketSelectTimeout);
            if ($changedStreams === false) {
                $this->logger()->warning('Unable to wait for a stream availability.');
                break;
            }
        }
        return $this->feedback;
    }

    /**
     * Parses binary tuples.
     *
     * @param  $binaryTuple @type string A binary tuple to parse.
     * @return @type array Array with timestamp, tokenLength and deviceToken keys.
     */
    protected function parseBinaryTuple($binaryTuple)
    {
        return unpack('Ntimestamp/ntokenLength/H*deviceToken', $binaryTuple);
    }
}
