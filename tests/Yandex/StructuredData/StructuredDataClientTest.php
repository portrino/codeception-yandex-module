<?php

namespace Codeception\Module\Tests\Yandex\StructuredData;

/*
 * This file is part of the Codeception Yandex Module project
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read
 * LICENSE file that was distributed with this source code.
 *
 */

use Codeception\Module\Yandex\StructuredData\StructuredDataClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class StructuredDataClientTest
 * @package Codeception\Module\Tests
 */
class StructuredDataClientTest extends TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|StructuredDataClient
     */
    protected $structuredDataClient;

    /**
     * @var string
     */
    protected $apiKey = '123456';

    /**
     * @var []
     */
    protected $data = [
        'json-ld' => [
            0 => [
                'http://schema.org/name' => [
                    0 => [
                        '@value' => 'Phoenix'
                    ]
                ]
            ],
            1 => [
                'http://schema.org/identifier' => [
                    0 => [
                        '@value' => '123456123456'
                    ]
                ],
                '#error' => [
                    0 => [
                        '#message' => 'WARNING: Error Message',
                        '#location' => '20:71',
                        '#error_code' => 'org_field_missing',
                        '#type' => 'yandex'
                    ]
                ]
            ],
            2 => [
                'http://schema.org/url' => [
                    0 => [
                        '@value' => 'https://github.com/portrino/codeception-yandex-module'
                    ]
                ],
                '#error' => [
                    0 => [
                        '#message' => 'WARNING: Error Message',
                        '#location' => '20:72',
                        '#error_code' => 'org_field_missing',
                        '#type' => 'yandex'
                    ]
                ]
            ]
        ]
    ];

    /**
     *
     */
    public function setUp()
    {
        $this->structuredDataClient = $this->getMockBuilder(StructuredDataClient::class)
            ->setMethods(
                [
                    'getClient',
                ]
            )
            ->setConstructorArgs(
                [
                    $this->apiKey
                ]
            )
            ->getMock();

        $response = [
            'id' => '123456123456sddsdsa456',
            'data' => $this->data
        ];

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200, [], json_encode($response))
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $this->structuredDataClient
            ->expects(static::any())
            ->method('getClient')
            ->willReturn($client);
    }

    /**
     * @test
     */
    public function getApiKey()
    {
        $apiKey = $this->structuredDataClient->getApiKey();
        static::assertEquals($this->apiKey, $apiKey);
    }

    /**
     * @test
     */
    public function setApiKey()
    {
        $this->structuredDataClient->setApiKey('abcdef');
        $apiKey = $this->structuredDataClient->getApiKey();
        static::assertEquals($apiKey, 'abcdef');
    }

    /**
     * @test
     */
    public function validateHtml()
    {
        $validationReponse = $this->structuredDataClient->validateHtml('foo');

        static::assertFalse($validationReponse->isValid());
        static::assertEquals('123456123456sddsdsa456', $validationReponse->getId());
        static::assertEquals('Phoenix', $validationReponse->getData()['json-ld'][0]['http://schema.org/name'][0]['@value']);
    }
}
