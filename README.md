ApnsPHP: Apple Push Notification & Feedback Provider
==========================

<p align="center">
	<img alt="Packagist Downloads" src="https://img.shields.io/packagist/dt/m2mobi/apns-php">	
	<img alt="Packagist PHP Version Support" src="https://img.shields.io/packagist/php-v/m2mobi/apns-php">
	<img alt="GitHub release (latest by date including pre-releases)" src="https://img.shields.io/github/v/release/M2mobi/ApnsPHP?include_prereleases">
	<a href="https://github.com/M2mobi/ApnsPHP/blob/master/LICENSE.txt"><img alt="GitHub license" src="https://img.shields.io/github/license/M2mobi/ApnsPHP"></a>
</p>

A **full set** of *open source* PHP classes to interact with the **Apple Push Notification service** for the iPhone, iPad and the iPod Touch.

- [Sample PHP Push code](sample_push.php)
- [Sample PHP Feedback code](sample_feedback.php)
- [Sample PHP Server code](sample_server.php)
- [Full APIs Documentation](https://m2mobi.github.io/ApnsPHP/html/index.html)
- [How to generate a Push Notification certificate and download the Entrust Root Authority certificate](Doc/CertificateCreation.md)
 

Why fork this?
-------

The old repo didn't have anyone maintaining it and we still want to send APNS notifications with minimal overhead.

Packagist
-------

https://packagist.org/packages/m2mobi/apns-php


Details
---------

In the Apple Push Notification Binary protocol there isn't a real-time feedback about the correctness of notifications pushed to the server. So, after each write to the server, the Push class waits for the "read stream" to change its status (or at least N microseconds); if it happened and the client socket receives an "end-of-file" from the server, the notification pushed to the server was broken, the Apple server has closed the connection and the client needs to reconnect to send other notifications still on the message queue.

To speed-up the sending activities the Push Server class can be used to create a Push Notification Server with many processes that reads a common message queue and sends parallel Push Notifications.

All client-server activities are based on the "on error, retry" pattern with customizable timeouts, retry times and retry intervals.

Requirements
-------------

PHP 7.2.0 or later with OpenSSL, Http/2, PCNTL, System V shared memory and semaphore support.
