<?php

namespace Codeception\Module\Tests;

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

use Codeception\Module\Yandex;
use Codeception\Module\Yandex\StructuredData\StructuredDataClient;
use Codeception\Module\Yandex\StructuredData\ValidationResponse;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class YandexTest
 * @package Codeception\Module\Tests
 */
class YandexTest extends TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Yandex
     */
    protected $yandex;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|StructuredDataClient
     */
    protected $structuredDataClient;

    /**
     * @var string
     */
    protected $htmlContent;

    /**
     * @var []
     */
    protected $data = [
        'json_ld' => [
            0 => [
                'http://schema.org/name' => [
                    0 => [
                        '_value' => 'Phoenix'
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
        $this->htmlContent = file_get_contents(
            __DIR__ . '/fixtures/test.html'
        );

        $validationResponse = $this->getMockBuilder(ValidationResponse::class)
            ->setMethods(
                [
                    'isValid',
                    'getErrorsFormatted',
                    'getData',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $validationResponse
            ->expects(static::any())
            ->method('isValid')
            ->willReturn(true);

        $validationResponse
            ->expects(static::any())
            ->method('getErrorsFormatted')
            ->willReturn([]);

        $validationResponse
            ->expects(static::any())
            ->method('getData')
            ->willReturn($this->data);

        $this->structuredDataClient = $this->getMockBuilder(StructuredDataClient::class)
            ->setMethods(
                [
                    'validateHtml',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->structuredDataClient
            ->expects(static::any())
            ->method('validateHtml')
            ->will(
                static::returnValueMap(
                    [
                        [$this->htmlContent, false, $validationResponse],
                        [$this->htmlContent, true, $validationResponse]
                    ]
                )
            );

        $this->yandex = $this->getMockBuilder(Yandex::class)
            ->setMethods(
                [
                    'getResponseContent',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->yandex
            ->expects(static::any())
            ->method('getResponseContent')
            ->willReturn($this->htmlContent);

        $this->yandex->setStructuredDataClient($this->structuredDataClient);
    }

    /**
     * @test
     */
    public function seeResponseContainsValidStructuredDataMarkup()
    {
        $this->yandex->seeResponseContainsValidStructuredDataMarkup();
    }

    /**
     * @test
     */
    public function seeResponseContainsValidJsonLdMarkup()
    {
        $this->yandex->seeResponseContainsValidJsonLdMarkup();
    }

    /**
     * @test
     */
    public function seeResponseContainsValidMicrodataMarkup()
    {
        $this->yandex->seeResponseContainsValidMicrodataMarkup();
    }

    /**
     * @test
     */
    public function seeResponseContainsValidMicroformatMarkup()
    {
        $this->yandex->seeResponseContainsValidMicroformatMarkup();
    }

    /**
     * @test
     */
    public function grabStructuredDataFromApiResponse()
    {
        $structuredData = $this->yandex->grabStructuredDataFromApiResponse();
        static::assertArrayHasKey('json_ld', $structuredData);
        static::assertArrayHasKey('http://schema.org/name', $structuredData['json_ld'][0]);
    }

    /**
     * @test
     */
    public function grabStructuredDataFromApiResponseByJsonPath()
    {
        $name = $this->yandex->grabStructuredDataFromApiResponseByJsonPath('json_ld.0.http___schema_org_name.0._value')[0];
        static::assertEquals('Phoenix', $name);
    }
}
