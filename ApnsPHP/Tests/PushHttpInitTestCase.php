<?php

/**
 * This file contains the PushHttpInitTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2021 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace ApnsPHP\Tests;

use ApnsPHP\Exception;
use Lcobucci\JWT\Token\DataSet;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Token\Signature;

/**
 * This class contains tests for the httpInit function
 *
 * @covers \ApnsPHP\Push
 */
class PushHttpInitTestCase extends PushTestCase
{
    /**
     * Test that httpInit() succeeds with certificate
     *
     * @covers \ApnsPHP\Push::httpInit
     */
    public function testHttpInitSucceedsWithCertificate(): void
    {
        $this->setReflectionPropertyValue('providerCertFile', 'cert.pem');
        $this->setReflectionPropertyValue('logger', $this->logger);

        $expectations = [
            'Trying to initialize HTTP/2 backend...',
            'Initializing HTTP/2 backend with certificate.',
            'Initialized HTTP/2 backend.',
        ];

        $invokedCount = self::exactly(count($expectations));

        $this->logger->expects($invokedCount)
                     ->method('info')
                     ->willReturnCallback(function ($parameters) use ($invokedCount, $expectations) {
                         $currentInvocationCount = $invokedCount->numberOfInvocations();
                         $currentExpectation = $expectations[$currentInvocationCount - 1];

                         $this->assertSame($currentExpectation, $parameters);
                     });

        $method = $this->getReflectionMethod('httpInit');
        $result = $method->invoke($this->class);

        $this->assertTrue($result);
    }

    /**
     * Test that httpInit() succeeds with key
     *
     * @covers \ApnsPHP\Push::httpInit
     */
    public function testHttpInitSucceedsWithKey(): void
    {
        $this->setReflectionPropertyValue('providerCertFile', 'key.p8');
        $this->setReflectionPropertyValue('providerTeamId', 'TheTeam');
        $this->setReflectionPropertyValue('providerKeyId', 'TheKey');
        $this->setReflectionPropertyValue('logger', $this->logger);

        $key = $this->getMockBuilder('Lcobucci\JWT\Signer\Key')
                    ->disableOriginalConstructor()
                    ->getMock();

        $builder = $this->getMockBuilder('Lcobucci\JWT\Builder')
                        ->getMock();

        $token = new Plain(
            new DataSet([ 'headers' => 'foo' ], 'eHeaders'),
            new DataSet([ 'claims' => 'bar' ], 'eClaims'),
            new Signature('signature', 'eSignature'),
        );

        $this->mock_method([ 'Lcobucci\JWT\Signer\Key\InMemory', 'file' ], fn() => $key);

        $this->mock_method([ 'Lcobucci\JWT\Configuration', 'builder' ], fn() => $builder);

        $builder->expects($this->once())
                ->method('issuedBy')
                ->with('TheTeam')
                ->willReturnSelf();

        $builder->expects($this->once())
                ->method('issuedAt')
                ->with($this->isInstanceof('DateTimeImmutable'))
                ->willReturnSelf();

        $builder->expects($this->once())
                ->method('withHeader')
                ->with('kid', 'TheKey')
                ->willReturnSelf();

        $builder->expects($this->once())
                ->method('getToken')
                ->with($this->isInstanceOf('Lcobucci\JWT\Signer\Ecdsa\Sha256'), $key)
                ->willReturn($token);

        $expectations = [
            'Trying to initialize HTTP/2 backend...',
            'Initializing HTTP/2 backend with key.',
            'Initialized HTTP/2 backend.',
        ];

        $invokedCount = self::exactly(count($expectations));

        $this->logger->expects($invokedCount)
                     ->method('info')
                     ->willReturnCallback(function ($parameters) use ($invokedCount, $expectations) {
                         $currentInvocationCount = $invokedCount->numberOfInvocations();
                         $currentExpectation = $expectations[$currentInvocationCount - 1];

                         $this->assertSame($currentExpectation, $parameters);
                     });

        $method = $this->getReflectionMethod('httpInit');
        $result = $method->invoke($this->class);

        $this->assertTrue($result);

        $this->unmock_method([ 'Lcobucci\JWT\Signer\Key\InMemory', 'file' ]);
        $this->unmock_method([ 'Lcobucci\JWT\Configuration', 'builder' ]);
    }

    /**
     * Test that httpInit() throws an exception when curl_setopt_array() fails
     *
     * @covers \ApnsPHP\Push::httpInit
     */
    public function testHttpInitThrowsExceptionOnCurlSetoptFail(): void
    {
        $this->setReflectionPropertyValue('providerCertFile', 'key.p8');
        $this->setReflectionPropertyValue('providerTeamId', 'TheTeam');
        $this->setReflectionPropertyValue('providerKeyId', 'TheKey');
        $this->setReflectionPropertyValue('logger', $this->logger);

        $this->mock_function('curl_setopt_array', fn() => false);

        $key = $this->getMockBuilder('Lcobucci\JWT\Signer\Key')
                    ->disableOriginalConstructor()
                    ->getMock();

        $builder = $this->getMockBuilder('Lcobucci\JWT\Builder')
                        ->getMock();

        $token = new Plain(
            new DataSet([ 'headers' => 'foo' ], 'eHeaders'),
            new DataSet([ 'claims' => 'bar' ], 'eClaims'),
            new Signature('signature', 'eSignature'),
        );

        $this->mock_method([ 'Lcobucci\JWT\Signer\Key\InMemory', 'file' ], fn() => $key);

        $this->mock_method([ 'Lcobucci\JWT\Configuration', 'builder' ], fn() => $builder);

        $builder->expects($this->once())
                ->method('issuedBy')
                ->with('TheTeam')
                ->willReturnSelf();

        $builder->expects($this->once())
                ->method('issuedAt')
                ->with($this->isInstanceof('DateTimeImmutable'))
                ->willReturnSelf();

        $builder->expects($this->once())
                ->method('withHeader')
                ->with('kid', 'TheKey')
                ->willReturnSelf();

        $builder->expects($this->once())
                ->method('getToken')
                ->with($this->isInstanceOf('Lcobucci\JWT\Signer\Ecdsa\Sha256'), $key)
                ->willReturn($token);

        $expectations = [
            'Trying to initialize HTTP/2 backend...',
            'Initializing HTTP/2 backend with key.',
        ];

        $invokedCount = self::exactly(count($expectations));

        $this->logger->expects($invokedCount)
                     ->method('info')
                     ->willReturnCallback(function ($parameters) use ($invokedCount, $expectations) {
                         $currentInvocationCount = $invokedCount->numberOfInvocations();
                         $currentExpectation = $expectations[$currentInvocationCount - 1];

                         $this->assertSame($currentExpectation, $parameters);
                     });

        $this->expectException('ApnsPHP\Exception');
        $this->expectExceptionMessage('Unable to initialize HTTP/2 backend.');

        $method = $this->getReflectionMethod('httpInit');

        # phpstan doesn't detect the potential throw through ReflectionMethod.
        # Verified that by making the method public and calling it directly
        # it's detected just fine.
        # May be https://github.com/phpstan/phpstan/issues/7719
        try {
            $method->invoke($this->class);
        } catch (Exception $e) { /* @phpstan-ignore-line */
            $this->unmock_function('curl_setopt_array');
            $this->unmock_method([ 'Lcobucci\JWT\Signer\Key\InMemory', 'file' ]);
            $this->unmock_method([ 'Lcobucci\JWT\Configuration', 'builder' ]);

            throw $e;
        }
    }
}
