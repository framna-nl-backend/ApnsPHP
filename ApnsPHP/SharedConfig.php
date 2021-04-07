<?php

/**
 * @file
 * SharedConfig class definition.
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

/**
 * @mainpage
 *
 * @li ApnsPHP on GitHub: https://github.com/immobiliare/ApnsPHP
 */

namespace ApnsPHP;

use DateTimeImmutable;
use ApnsPHP\Log\EmbeddedLogger;
use Psr\Log\LoggerInterface;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Ecdsa\Sha256;
use Lcobucci\JWT\Configuration;

/**
 * Abstract class: this is the superclass for all Apple Push Notification Service
 * classes.
 *
 * This class is responsible for the connection to the Apple Push Notification Service
 * and Feedback.
 *
 * @see http://tinyurl.com/ApplePushNotificationService
 */
abstract class SharedConfig
{
    /**< @type integer Production environment. */
    public const ENVIRONMENT_PRODUCTION = 0;

    /**< @type integer Sandbox environment. */
    public const ENVIRONMENT_SANDBOX = 1;

    /**< @type integer Binary Provider API. */
    public const PROTOCOL_BINARY = 0;

    /**< @type integer APNs Provider API. */
    public const PROTOCOL_HTTP   = 1;

    /**< @type integer Device token length. */
    public const DEVICE_BINARY_SIZE = 32;

    /**< @type integer Default write interval in micro seconds. */
    public const WRITE_INTERVAL = 10000;

    /**< @type integer Default connect retry interval in micro seconds. */
    public const CONNECT_RETRY_INTERVAL = 1000000;

    /**< @type integer Default socket select timeout in micro seconds. */
    public const SOCKET_SELECT_TIMEOUT = 1000000;

    /**< @type array Container for service URLs environments. */
    protected $serviceURLs = array();

    /**< @type array Container for HTTP/2 service URLs environments. */
    protected $HTTPServiceURLs = array();

    /**< @type integer Active environment. */
    protected $environment;

    /**< @type integer Active protocol. */
    protected $protocol;

    /**< @type integer Connect timeout in seconds. */
    protected $connectTimeout;

    /**< @type integer Connect retry times. */
    protected $connectRetryTimes = 3;

    /**< @type string Provider certificate file with key (Bundled PEM). */
    protected $providerCertFile;

    /**< @type string Provider certificate passphrase. */
    protected $providerCertPassphrase;

    /**< @type string|null Provider Authentication token. */
    protected $providerToken;

    /**< @type string|null Apple Team Identifier. */
    protected $providerTeamId;

    /**< @type string|null Apple Key Identifier. */
    protected $providerKeyId;

    /**< @type string Root certification authority file. */
    protected $rootCertAuthorityFile;

    /**< @type integer Write interval in micro seconds. */
    protected $writeInterval;

    /**< @type integer Connect retry interval in micro seconds. */
    protected $connectRetryInterval;

    /**< @type integer Socket select timeout in micro seconds. */
    protected $socketSelectTimeout;

    /**< @type Psr\Log\LoggerInterface Logger. */
    protected $logger;

    /**< @type resource SSL Socket. */
    protected $hSocket;

    /**
     * Constructor.
     *
     * @param  $environment @type integer Environment.
     * @param  $providerCertificateFile @type string Provider certificate file
     *         with key (Bundled PEM).
     * @param  $protocol @type integer Protocol.
     */
    public function __construct($environment, $providerCertificateFile, $protocol = self::PROTOCOL_BINARY)
    {
        if ($environment != self::ENVIRONMENT_PRODUCTION && $environment != self::ENVIRONMENT_SANDBOX) {
            throw new Exception(
                "Invalid environment '{$environment}'"
            );
        }
        $this->environment = $environment;

        if (!is_readable($providerCertificateFile)) {
            throw new Exception(
                "Unable to read certificate file '{$providerCertificateFile}'"
            );
        }
        $this->providerCertFile = $providerCertificateFile;

        if ($protocol != self::PROTOCOL_BINARY && $protocol != self::PROTOCOL_HTTP) {
            throw new Exception(
                "Invalid protocol '{$protocol}'"
            );
        }
        $this->protocol = $protocol;

        $this->connectTimeout = ini_get("default_socket_timeout");
        $this->writeInterval = self::WRITE_INTERVAL;
        $this->connectRetryInterval = self::CONNECT_RETRY_INTERVAL;
        $this->socketSelectTimeout = self::SOCKET_SELECT_TIMEOUT;
    }

    /**
     * Set the Logger instance to use for logging purpose.
     *
     * The default logger is EmbeddedLogger, an instance
     * of LoggerInterface that simply print to standard
     * output log messages.
     *
     * To set a custom logger you have to implement LoggerInterface
     * and use setLogger, otherwise standard logger will be used.
     *
     * @param  $logger @type LoggerInterface Logger instance.
     * @see Psr\Log\LoggerInterface
     * @see EmbeddedLogger
     *
     */
    public function setLogger(LoggerInterface $logger)
    {
        if (!is_object($logger)) {
            throw new Exception(
                "The logger should be an instance of 'Psr\Log\LoggerInterface'"
            );
        }
        if (!($logger instanceof LoggerInterface)) {
            throw new Exception(
                "Unable to use an instance of '" . get_class($logger) . "' as logger: " .
                "a logger must implements 'Psr\Log\LoggerInterface'."
            );
        }
        $this->logger = $logger;
    }

    /**
     * Get the Logger instance.
     *
     * @return @type Psr\Log\LoggerInterface Current Logger instance.
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Set the Provider Certificate passphrase.
     *
     * @param  $providerCertPassphrase @type string Provider Certificate
     *         passphrase.
     */
    public function setProviderCertPassphrase($providerCertPassphrase)
    {
        $this->providerCertPassphrase = $providerCertPassphrase;
    }

    /**
     * Set the Team Identifier.
     *
     * @param  string $teamId Apple Team Identifier.
     */
    public function setTeamId($teamId)
    {
        $this->providerTeamId = $teamId;
    }

    /**
     * Set the Key Identifier.
     *
     * @param  string $keyId Apple Key Identifier.
     */
    public function setKeyId($keyId)
    {
        $this->providerKeyId = $keyId;
    }

    /**
     * Set the Root Certification Authority file.
     *
     * Setting the Root Certification Authority file automatically set peer verification
     * on connect.
     *
     * @see http://tinyurl.com/GeneralProviderRequirements
     * @see http://www.entrust.net/
     * @see https://www.entrust.net/downloads/root_index.cfm
     *
     * @param  $rootCertificationAuthorityFile @type string Root Certification
     *         Authority file.
     */
    public function setRootCertificationAuthority($rootCertificationAuthorityFile)
    {
        if (!is_readable($rootCertificationAuthorityFile)) {
            throw new Exception(
                "Unable to read Certificate Authority file '{$rootCertificationAuthorityFile}'"
            );
        }
        $this->rootCertAuthorityFile = $rootCertificationAuthorityFile;
    }

    /**
     * Get the Root Certification Authority file path.
     *
     * @return @type string Current Root Certification Authority file path.
     */
    public function getCertificateAuthority()
    {
        return $this->rootCertAuthorityFile;
    }

    /**
     * Set the write interval.
     *
     * After each socket write operation we are sleeping for this
     * time interval. To speed up the sending operations, use Zero
     * as parameter but some messages may be lost.
     *
     * @param  $writeInterval @type integer Write interval in micro seconds.
     */
    public function setWriteInterval($writeInterval)
    {
        $this->writeInterval = (int)$writeInterval;
    }

    /**
     * Get the write interval.
     *
     * @return @type integer Write interval in micro seconds.
     */
    public function getWriteInterval()
    {
        return $this->writeInterval;
    }

    /**
     * Set the connection timeout.
     *
     * The default connection timeout is the PHP internal value "default_socket_timeout".
     * @see http://php.net/manual/en/filesystem.configuration.php
     *
     * @param  $timeout @type integer Connection timeout in seconds.
     */
    public function setConnectTimeout($timeout)
    {
        $this->connectTimeout = (int)$timeout;
    }

    /**
     * Get the connection timeout.
     *
     * @return @type integer Connection timeout in seconds.
     */
    public function getConnectTimeout()
    {
        return $this->connectTimeout;
    }

    /**
     * Set the connect retry times value.
     *
     * If the client is unable to connect to the server retries at least for this
     * value. The default connect retry times is 3.
     *
     * @param  $retryTimes @type integer Connect retry times.
     */
    public function setConnectRetryTimes($retryTimes)
    {
        $this->connectRetryTimes = (int)$retryTimes;
    }

    /**
     * Get the connect retry time value.
     *
     * @return @type integer Connect retry times.
     */
    public function getConnectRetryTimes()
    {
        return $this->connectRetryTimes;
    }

    /**
     * Set the connect retry interval.
     *
     * If the client is unable to connect to the server retries at least for ConnectRetryTimes
     * and waits for this value between each attempts.
     *
     * @param  $retryInterval @type integer Connect retry interval in micro seconds.
     *@see setConnectRetryTimes
     *
     */
    public function setConnectRetryInterval($retryInterval)
    {
        $this->connectRetryInterval = (int)$retryInterval;
    }

    /**
     * Get the connect retry interval.
     *
     * @return @type integer Connect retry interval in micro seconds.
     */
    public function getConnectRetryInterval()
    {
        return $this->connectRetryInterval;
    }

    /**
     * Set the TCP socket select timeout.
     *
     * After writing to socket waits for at least this value for read stream to
     * change status.
     *
     * In Apple Push Notification protocol there isn't a real-time
     * feedback about the correctness of notifications pushed to the server; so after
     * each write to server waits at least SocketSelectTimeout. If, during this
     * time, the read stream change its status and socket received an end-of-file
     * from the server the notification pushed to server was broken, the server
     * has closed the connection and the client needs to reconnect.
     *
     * @see http://php.net/stream_select
     *
     * @param  $selectTimeout @type integer Socket select timeout in micro seconds.
     */
    public function setSocketSelectTimeout($selectTimeout)
    {
        $this->socketSelectTimeout = (int)$selectTimeout;
    }

    /**
     * Get the TCP socket select timeout.
     *
     * @return @type integer Socket select timeout in micro seconds.
     */
    public function getSocketSelectTimeout()
    {
        return $this->socketSelectTimeout;
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
    public function connect()
    {
        $connected = false;
        $retry = 0;
        while (!$connected) {
            try {
                $connected = $this->protocol === self::PROTOCOL_HTTP ?
                    $this->httpInit() : $this->binaryConnect($this->serviceURLs[$this->environment]);
            } catch (Exception $e) {
                $this->logger()->error($e->getMessage());
                if ($retry >= $this->connectRetryTimes) {
                    throw $e;
                } else {
                    $this->logger()->info(
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
     * @return @type boolean True if successful disconnected.
     */
    public function disconnect()
    {
        if (is_resource($this->hSocket)) {
            $this->logger()->info('Disconnected.');
            if ($this->protocol === self::PROTOCOL_HTTP) {
                curl_close($this->hSocket);
                return true;
            } else {
                return fclose($this->hSocket);
            }
        }
        return false;
    }

    /**
     * Initializes cURL, the HTTP/2 backend used to connect to Apple Push Notification
     * service server via HTTP/2 API protocol.
     *
     * @return @type boolean True if successful initialized.
     */
    protected function httpInit()
    {
        $this->logger()->info("Trying to initialize HTTP/2 backend...");

        $this->hSocket = curl_init();
        if (!$this->hSocket) {
            throw new Exception(
                "Unable to initialize HTTP/2 backend."
            );
        }

        if (!defined('CURL_HTTP_VERSION_2_0')) {
            define('CURL_HTTP_VERSION_2_0', 3);
        }
        $curlOpts = array(
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'ApnsPHP',
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_VERBOSE => false
        );

        if (strpos($this->providerCertFile, '.pem') !== false) {
            $this->logger()->info("Initializing HTTP/2 backend with certificate.");
            $curlOpts[CURLOPT_SSLCERT] = $this->providerCertFile;
            $curlOpts[CURLOPT_SSLCERTPASSWD] = empty($this->providerCertPassphrase) ?
                null : $this->providerCertPassphrase;
        }

        if (strpos($this->providerCertFile, '.p8') !== false) {
            $this->logger()->info("Initializing HTTP/2 backend with key.");
            $this->providerToken = $this->getJsonWebToken();
        }

        if (!curl_setopt_array($this->hSocket, $curlOpts)) {
            throw new Exception(
                "Unable to initialize HTTP/2 backend."
            );
        }

        $this->logger()->info("Initialized HTTP/2 backend.");

        return true;
    }

    /**
     * @return string
     */
    protected function getJsonWebToken()
    {
        $key = new Key\LocalFileReference('file://' . $this->providerCertFile);
        return (string) Configuration::forUnsecuredSigner()->builder()
            ->issuedBy($this->providerTeamId)
            ->issuedAt(new DateTimeImmutable())
            ->withHeader('kid', $this->providerKeyId)
            ->getToken(new Sha256(), $key);
    }

    /**
     * Connects to Apple Push Notification service server via binary protocol.
     *
     * @return @type boolean True if successful connected.
     */
    protected function binaryConnect($URL)
    {
        $this->logger()->info("Trying {$URL}...");
        $URL = $this->serviceURLs[$this->environment];

        $this->logger()->info("Trying {$URL}...");

        /**
         * @see http://php.net/manual/en/context.ssl.php
         */
        $streamContext = stream_context_create(array('ssl' => array(
            'verify_peer' => isset($this->rootCertAuthorityFile),
            'cafile' => $this->rootCertAuthorityFile,
            'local_cert' => $this->providerCertFile
        )));

        if (!empty($this->providerCertPassphrase)) {
            stream_context_set_option(
                $streamContext,
                'ssl',
                'passphrase',
                $this->providerCertPassphrase
            );
        }

        $this->hSocket = @stream_socket_client(
            $URL,
            $errorCode,
            $errorMessage,
            $this->connectTimeout,
            STREAM_CLIENT_CONNECT,
            $streamContext
        );

        if (!$this->hSocket) {
            throw new Exception(
                "Unable to connect to '{$URL}': {$errorMessage} ({$errorCode})"
            );
        }

        stream_set_blocking($this->hSocket, 0);
        stream_set_write_buffer($this->hSocket, 0);

        $this->logger()->info("Connected to {$URL}.");

        return true;
    }

    /**
     * Return the Logger (with lazy loading)
     */
    protected function logger()
    {
        if (!isset($this->logger)) {
            $this->logger = new EmbeddedLogger();
        }

        return $this->logger;
    }
}
