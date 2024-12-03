<?php

/**
 * SPDX-FileCopyrightText: Copyright 2010 Aldo Armiento (aldo.armiento@gmail.com)
 * SPDX-FileCopyrightText: Copyright 2021 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace ApnsPHP;

use ApnsPHP\Push\Exception;
use CurlHandle;
use DateTimeImmutable;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Ecdsa\MultibyteStringConverter;
use Lcobucci\JWT\Signer\Ecdsa\Sha256;
use Lcobucci\JWT\Configuration;
use Psr\Log\LoggerInterface;

/**
 * The Push Notification Provider.
 *
 * The class manages a message queue and sends notifications payload to Apple Push
 * Notification Service.
 */
class Push
{
    /**
     * Device token length.
     * @var int
     */
    public const DEVICE_BINARY_SIZE = 32;

    /**
     * Default write interval in micro seconds.
     * @var int
     */
    public const WRITE_INTERVAL = 10000;

    /**
     * Default connect retry interval in micro seconds.
     * @var int
     */
    public const CONNECT_RETRY_INTERVAL = 1000000;

    /**
     * Payload command.
     * @var int
     */
    protected const COMMAND_PUSH = 1;

    /**
     * Error-response packet size.
     * @var int
     */
    protected const ERROR_RESPONSE_SIZE = 6;

    /**
     * Error-response command code.
     * @var int
     */
    protected const ERROR_RESPONSE_COMMAND = 8;

    /**
     * Status code for internal error (not Apple).
     * @var int
     */
    protected const STATUS_CODE_INTERNAL_ERROR = 999;

    /**
     * Active environment.
     * @var Environment
     */
    protected Environment $environment;

    /**
     * Connect timeout in seconds.
     * @var int
     */
    protected int $connectTimeout = 10;

    /**
     * Connect retry times.
     * @var int
     */
    protected int $connectRetryTimes = 3;

    /**
     * Provider certificate file with key (Bundled PEM).
     * @var string
     */
    protected string $providerCertFile;

    /**
     * Provider certificate passphrase.
     * @var string
     */
    protected string $providerCertPassphrase;

    /**
     * Provider Authentication token.
     * @var string|null
     */
    protected ?string $providerToken;

    /**
     * Apple Team Identifier.
     * @var string|null
     */
    protected ?string $providerTeamId;

    /**
     * Apple Key Identifier.
     * @var string|null
     */
    protected ?string $providerKeyId;

    /**
     * Write interval in micro seconds.
     * @var int
     */
    protected int $writeInterval;

    /**
     * Connect retry interval in micro seconds.
     * @var int
     */
    protected int $connectRetryInterval;

    /**
     * Logger.
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * SSL Socket.
     * @var CurlHandle|null
     */
    protected ?CurlHandle $hSocket;

    /**
     * HTTP/2 Error-response messages.
     * @var array<int,string>
     */
    protected array $HTTPErrorResponseMessages = [
        200 => 'Success',
        400 => 'Bad request',
        403 => 'There was an error with the certificate',
        405 => 'The request used a bad :method value. Only POST requests are supported',
        410 => 'The device token is no longer active for the topic',
        413 => 'The notification payload was too large',
        429 => 'The server received too many requests for the same device token',
        500 => 'Internal server error',
        503 => 'The server is shutting down and unavailable',
        self::STATUS_CODE_INTERNAL_ERROR => 'Internal error'
    ];

    /**
     * Send retry times.
     * @var int
     */
    protected int $sendRetryTimes = 3;

    /**
     * Message queue.
     * @var array
     */
    protected array $messageQueue = [];

    /**
     * Error container.
     * @var array
     */
    protected array $errors = [];

    /**
     * Constructor.
     *
     * @param Environment     $environment             Environment.
     * @param string          $providerCertificateFile Provider certificate file with key (Bundled PEM/P8).
     * @param LoggerInterface $logger                  A Logger implementing PSR-3
     */
    public function __construct(Environment $environment, string $providerCertificateFile, LoggerInterface $logger)
    {
        $this->environment = $environment;

        if (!is_readable($providerCertificateFile)) {
            throw new Exception(
                "Unable to read certificate file '{$providerCertificateFile}'"
            );
        }
        $this->providerCertFile = $providerCertificateFile;

        $this->writeInterval = self::WRITE_INTERVAL;
        $this->connectRetryInterval = self::CONNECT_RETRY_INTERVAL;

        $this->logger  = $logger;
        $this->hSocket = null;
    }

    /**
     * Set the send retry times value.
     *
     * If the client is unable to send a payload to to the server retries at least
     * for this value. The default send retry times is 3.
     *
     * @param int $retryTimes Send retry times.
     */
    public function setSendRetryTimes(int $retryTimes): void
    {
        $this->sendRetryTimes = $retryTimes;
    }

    /**
     * Get the send retry time value.
     *
     * @return int Send retry times.
     */
    public function getSendRetryTimes(): int
    {
        return $this->sendRetryTimes;
    }

    /**
     * Set the Provider Certificate passphrase.
     *
     * @param string $providerCertPassphrase Provider Certificate passphrase.
     */
    public function setProviderCertificatePassphrase(string $providerCertPassphrase): void
    {
        $this->providerCertPassphrase = $providerCertPassphrase;
    }

    /**
     * Set the Team Identifier.
     *
     * @param string $teamId Apple Team Identifier.
     */
    public function setTeamId(string $teamId): void
    {
        $this->providerTeamId = $teamId;
    }

    /**
     * Set the Key Identifier.
     *
     * @param string $keyId Apple Key Identifier.
     */
    public function setKeyId(string $keyId): void
    {
        $this->providerKeyId = $keyId;
    }

    /**
     * Set the write interval.
     *
     * After each socket write operation we are sleeping for this
     * time interval. To speed up the sending operations, use Zero
     * as parameter but some messages may be lost.
     *
     * @param int $writeInterval Write interval in micro seconds.
     */
    public function setWriteInterval(int $writeInterval): void
    {
        $this->writeInterval = $writeInterval;
    }

    /**
     * Get the write interval.
     *
     * @return int Write interval in micro seconds.
     */
    public function getWriteInterval(): int
    {
        return $this->writeInterval;
    }

    /**
     * Set the connection timeout.
     *
     * The default connection timeout is the PHP internal value "default_socket_timeout".
     * @see http://php.net/manual/en/filesystem.configuration.php
     *
     * @param int $timeout Connection timeout in seconds.
     */
    public function setConnectTimeout(int $timeout): void
    {
        $this->connectTimeout = $timeout;
    }

    /**
     * Get the connection timeout.
     *
     * @return int Connection timeout in seconds.
     */
    public function getConnectTimeout(): int
    {
        return $this->connectTimeout;
    }

    /**
     * Set the connect retry times value.
     *
     * If the client is unable to connect to the server retries at least for this
     * value. The default connect retry times is 3.
     *
     * @param int $retryTimes Connect retry times.
     */
    public function setConnectRetryTimes(int $retryTimes): void
    {
        $this->connectRetryTimes = $retryTimes;
    }

    /**
     * Get the connect retry time value.
     *
     * @return int Connect retry times.
     */
    public function getConnectRetryTimes(): int
    {
        return $this->connectRetryTimes;
    }

    /**
     * Set the connect retry interval.
     *
     * If the client is unable to connect to the server retries at least for ConnectRetryTimes
     * and waits for this value between each attempts.
     *
     * @see setConnectRetryTimes
     *
     * @param int $retryInterval Connect retry interval in micro seconds.
     */
    public function setConnectRetryInterval(int $retryInterval): void
    {
        $this->connectRetryInterval = $retryInterval;
    }

    /**
     * Get the connect retry interval.
     *
     * @return int Connect retry interval in micro seconds.
     */
    public function getConnectRetryInterval(): int
    {
        return $this->connectRetryInterval;
    }

    /**
     * Connects to Apple Push Notification service server.
     *
     * Retries ConnectRetryTimes if unable to connect and waits setConnectRetryInterval
     * between each attempts.
     *
     * @see setConnectRetryInterval
     * @see setConnectRetryTimes
     */
    public function connect(): void
    {
        $connected = false;
        $retry = 0;
        while (!$connected) {
            try {
                $connected = $this->httpInit();
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
                if ($retry >= $this->connectRetryTimes) {
                    throw $e;
                } else {
                    $this->logger->info(
                        "Retry to connect (" . ($retry + 1) .
                        "/{$this->connectRetryTimes})..."
                    );
                    usleep($this->connectRetryInterval);
                }
            }
            $retry++;
        }
    }

    /**
     * Disconnects from Apple Push Notifications service server.
     *
     * @return bool True if successful disconnected.
     */
    public function disconnect(): bool
    {
        if (is_object($this->hSocket)) {
            $this->logger->info('Disconnected.');
            curl_close($this->hSocket);
            unset($this->hSocket); // curl_close($handle) has no effect in PHP >= 8.0
            return true;
        }
        return false;
    }

    /**
     * Initializes cURL, the HTTP/2 backend used to connect to Apple Push Notification
     * service server via HTTP/2 API protocol.
     *
     * @return bool True if successful initialized.
     */
    protected function httpInit(): bool
    {
        $this->logger->info("Trying to initialize HTTP/2 backend...");

        # curl_init() without arguments only really returns false in OOM scenarios,
        # or an error in the DNS resolver initialization. phpstan says we can
        # skip the false check, and I tend to agree.
        $this->hSocket = curl_init();

        $curlOpts = [
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'ApnsPHP',
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_VERBOSE => false
        ];

        if (strpos($this->providerCertFile, '.pem') !== false) {
            $this->logger->info("Initializing HTTP/2 backend with certificate.");
            $curlOpts[CURLOPT_SSLCERT] = $this->providerCertFile;
            $curlOpts[CURLOPT_SSLCERTPASSWD] = empty($this->providerCertPassphrase) ?
                null : $this->providerCertPassphrase;
        }

        if (strpos($this->providerCertFile, '.p8') !== false) {
            $this->logger->info("Initializing HTTP/2 backend with key.");
            $this->providerToken = $this->getJsonWebToken();
        }

        if (!curl_setopt_array($this->hSocket, $curlOpts)) {
            throw new Exception(
                "Unable to initialize HTTP/2 backend."
            );
        }

        $this->logger->info("Initialized HTTP/2 backend.");

        return true;
    }

    /**
     * Get a JSON Web Token for authentication when using .p8 certificates.
     *
     * @return string JSON Web Token
     */
    protected function getJsonWebToken(): string
    {
        $key          = InMemory::file($this->providerCertFile);
        $signer       = new Sha256(new MultibyteStringConverter());
        $tokenBuilder = Configuration::forSymmetricSigner($signer, $key)->builder();

        return $tokenBuilder->issuedBy($this->providerTeamId)
                            ->issuedAt(new DateTimeImmutable())
                            ->withHeader('kid', $this->providerKeyId)
                            ->getToken($signer, $key)
                            ->toString();
    }

    /**
     * Adds a message to the message queue.
     *
     * @param Message $message The message.
     */
    public function add(Message $message): void
    {
        $messagePayload = $message->getPayload();
        $recipients = $message->getRecipientsNumber();

        $messageQueueLen = count($this->messageQueue);
        for ($i = 0; $i < $recipients; $i++) {
            $messageId = $messageQueueLen + $i + 1;
            $messages = [
                'MESSAGE' => $message->selfForRecipient($i),
                'ERRORS' => []
            ];
            $this->messageQueue[$messageId] = $messages;
        }
    }

    /**
     * Sends all messages in the message queue to Apple Push Notification Service.
     */
    public function send(): void
    {
        if ($this->hSocket === null) {
            throw new Exception(
                'Not connected to Push Notification Service'
            );
        }

        if (empty($this->messageQueue)) {
            throw new Exception(
                'No notifications queued to be sent'
            );
        }

        $this->errors = [];
        $run = 1;
        while (($messageAmount = count($this->messageQueue)) > 0) {
            $this->logger->info("Sending messages queue, run #{$run}: $messageAmount message(s) left in queue.");

            $error = false;
            foreach ($this->messageQueue as $key => &$messages) {
                $message = $messages['MESSAGE'];
                $customIdentifier = (string)$message->getCustomIdentifier();
                $customIdentifier = sprintf(
                    '[custom identifier: %s]',
                    empty($customIdentifier) ? 'unset' : $customIdentifier
                );

                $errorAmount = 0;
                if (!empty($messages['ERRORS'])) {
                    foreach ($messages['ERRORS'] as $errors) {
                        if ($errors['statusCode'] == 0 || $errors['statusCode'] == 200) {
                            $this->logger->info(
                                "Message ID {$key} {$customIdentifier} has no error ({$errors['statusCode']}),
                                 removing from queue..."
                            );
                            $this->removeMessageFromQueue($key);
                            continue 2;
                        } elseif ($errors['statusCode'] > 200 && $errors['statusCode'] <= 413) {
                            $this->logger->warning(
                                "Message ID {$key} {$customIdentifier} has an unrecoverable error
                                 ({$errors['statusCode']}), removing from queue without retrying..."
                            );
                            $this->removeMessageFromQueue($key, true);
                            continue 2;
                        }
                    }
                    if (($errorAmount = count($messages['ERRORS'])) >= $this->sendRetryTimes) {
                        $this->logger->warning(
                            "Message ID {$key} {$customIdentifier} has {$errorAmount} errors, removing from queue..."
                        );
                        $this->removeMessageFromQueue($key, true);
                        continue;
                    }
                }

                $messageBytes = strlen($message->getPayload());
                $this->logger->debug("Sending message ID {$key} {$customIdentifier} (" . ($errorAmount + 1) .
                                        "/{$this->sendRetryTimes}): {$messageBytes} bytes.");

                $errorMessage = null;
                $reply        = '';

                if (!$this->httpSend($message, $reply)) {
                    $errorMessage = [
                        'identifier' => $key,
                        'statusCode' => curl_getinfo($this->hSocket, CURLINFO_HTTP_CODE),
                        'statusMessage' => $reply
                    ];
                }
                usleep($this->writeInterval);

                $error = $this->updateQueue($errorMessage);
                if ($error) {
                    break;
                }
            }

            if (!$error) {
                $this->messageQueue = [];
            }

            $run++;
        }
    }

    /**
     * Send a message using the HTTP/2 API protocol.
     *
     * @param Message $message The message.
     * @param string  $reply   The reply message.
     *
     * @return bool Success of API call
     */
    private function httpSend(Message $message, &$reply): bool
    {
        $headers = ['Content-Type: application/json'];
        if (!empty($message->getTopic())) {
            $headers[] = sprintf('apns-topic: %s', $message->getTopic());
        }
        if (!empty($message->getExpiry())) {
            $headers[] = sprintf('apns-expiration: %s', $message->getExpiry());
        }
        if (!empty($message->getPriority())) {
            $headers[] = sprintf('apns-priority: %s', $message->getPriority()->value);
        }
        if (!empty($message->getCollapseId())) {
            $headers[] = sprintf('apns-collapse-id: %s', $message->getCollapseId());
        }
        if (!empty($message->getCustomIdentifier())) {
            $headers[] = sprintf('apns-id: %s', $message->getCustomIdentifier());
        }
        if ($message->getPushType() !== null) {
            $headers[] = sprintf('apns-push-type: %s', $message->getPushType()->value);
        }
        if (!empty($this->providerToken)) {
            $headers[] = sprintf('Authorization: Bearer %s', $this->providerToken);
        }

        $curl_setopt_result = curl_setopt_array(
            $this->hSocket,
            [
                CURLOPT_POST => true,
                CURLOPT_URL => sprintf(
                    '%s/3/device/%s',
                    $this->environment->getUrl(),
                    $message->getRecipient()
                ),
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POSTFIELDS => $message->getPayload()
            ]
        );

        $curl_exec_result = curl_exec($this->hSocket);

        if (!is_bool($curl_exec_result)) {
            $reply = $curl_exec_result;
        }

        if ($curl_setopt_result !== true || $curl_exec_result === false) {
            $errorCode = curl_errno($this->hSocket);

            if ($errorCode > 0) {
                $this->logger->error('HTTP request error code: {code}; message: {message}.', [
                    'code' => $errorCode,
                    'message' => curl_error($this->hSocket),
                ]);
            }

            return false;
        }

        return curl_getinfo($this->hSocket, CURLINFO_HTTP_CODE) === 200;
    }

    /**
     * Returns messages in the message queue.
     *
     * When a message is successful sent or reached the maximum retry time is removed
     * from the message queue and inserted in the Errors container. Use the getErrors()
     * method to retrieve messages with delivery error(s).
     *
     * @param bool $empty Empty the message queue (optional).
     *
     * @return array Array of messages left on the queue.
     */
    public function getMessageQueue(bool $empty = true): array
    {
        $messages = $this->messageQueue;
        if ($empty) {
            $this->messageQueue = [];
        }
        return $messages;
    }

    /**
     * Returns messages not delivered to the end user because one (or more) error
     * occurred.
     *
     * @param bool $empty Empty the message container.
     *
     * @return array Array of messages not delivered because one or more errors occurred.
     */
    public function getErrors(bool $empty = true): array
    {
        $messages = $this->errors;
        if ($empty) {
            $this->errors = [];
        }
        return $messages;
    }

    /**
     * Checks for error message and deletes messages successfully sent from message queue.
     *
     * @param array $errorMessages The error message (optional).
     *
     * @return bool True if an error was received.
     */
    protected function updateQueue(?array $errorMessages = null): bool
    {
        if (!isset($errorMessages)) {
            return false;
        }

        $this->logger->error('Unable to send message ID ' .
            $errorMessages['identifier'] . ': ' .
            $errorMessages['statusMessage'] . ' (' . $errorMessages['statusCode'] . ').');

        $this->disconnect();

        foreach ($this->messageQueue as $key => &$message) {
            if ($key < $errorMessages['identifier']) {
                unset($this->messageQueue[$key]);
            } elseif ($key == $errorMessages['identifier']) {
                $message['ERRORS'][] = $errorMessages;
            } else {
                break;
            }
        }

        $this->connect();

        return true;
    }

    /**
     * Remove a message from the message queue.
     *
     * @param int  $messageId The Message ID.
     * @param bool $error     Insert the message in the Error container (optional).
     */
    protected function removeMessageFromQueue(int $messageId, bool $error = false): void
    {
        if ($messageId <= 0) {
            throw new Exception(
                'Message ID format is not valid.'
            );
        }
        if (!isset($this->messageQueue[$messageId])) {
            throw new Exception(
                "The Message ID {$messageId} does not exists."
            );
        }
        if ($error) {
            $this->errors[$messageId] = $this->messageQueue[$messageId];
        }
        unset($this->messageQueue[$messageId]);
    }
}
