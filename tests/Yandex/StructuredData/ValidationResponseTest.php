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

use Codeception\Module\Yandex;
use Codeception\Module\Yandex\StructuredData\StructuredDataClient;
use Codeception\Module\Yandex\StructuredData\ValidationResponse;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class YandexTest
 * @package Codeception\Module\Tests
 */
class ValidationResponseTest extends TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ValidationResponse
     */
    protected $validationResponse;

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
        $this->validationResponse = new ValidationResponse('123456', $this->data);
    }

    /**
     * @test
     */
    public function getData()
    {
        $data = $this->validationResponse->getData();
        static::assertArrayHasKey('json-ld', $data);
        static::assertArrayHasKey('http://schema.org/name', $data['json-ld'][0]);
    }

    /**
     * @test
     */
    public function setData()
    {
        $newData = $this->data;
        $newData['json-ld'][0]['http://schema.org/image'][0]['@value'] = 'foo.jpg';
        $this->validationResponse->setData($newData);
        $data = $this->validationResponse->getData();

        static::assertArrayHasKey('json-ld', $data);
        static::assertArrayHasKey('@value', $data['json-ld'][0]['http://schema.org/image'][0]);
    }

    /**
     * @test
     */
    public function isValid()
    {
        $isValid = $this->validationResponse->isValid();
        static::assertFalse($isValid);

        $isValid = $this->validationResponse->isValid(ValidationResponse::JSONLD);
        static::assertFalse($isValid);

        $newData = [
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
                    ]
                ],
                2 => [
                    'http://schema.org/url' => [
                        0 => [
                            '@value' => 'https://github.com/portrino/codeception-yandex-module'
                        ]
                    ]
                ]
            ]
        ];

        $this->validationResponse->setData($newData);

        $isValid = $this->validationResponse->isValid(ValidationResponse::JSONLD);
        static::assertTrue($isValid);
    }

    /**
     * @test
     */
    public function getErrors()
    {
        $errors = $this->validationResponse->getErrors();
        static::assertEquals(2, count($errors));

        static::assertArrayHasKey('#message', $errors[0]);
        static::assertArrayHasKey('#location', $errors[0]);
        static::assertArrayHasKey('#error_code', $errors[0]);
        static::assertArrayHasKey('#type', $errors[0]);

        static::assertEquals($errors[0]['#message'], 'WARNING: Error Message');

        $errors = $this->validationResponse->getErrors(ValidationResponse::JSONLD);
        static::assertEquals(2, count($errors));

        static::assertArrayHasKey('#message', $errors[0]);
        static::assertArrayHasKey('#location', $errors[0]);
        static::assertArrayHasKey('#error_code', $errors[0]);
        static::assertArrayHasKey('#type', $errors[0]);

        static::assertEquals($errors[0]['#message'], 'WARNING: Error Message');
    }

    /**
     * @test
     */
    public function getErrorsFormatted()
    {
        $errorsFormatted = $this->validationResponse->getErrorsFormatted();
        static::assertEquals(2, count($errorsFormatted));
        static::assertEquals($errorsFormatted[0], 'WARNING: Error Message;20:71;org_field_missing;yandex');
    }

    /**
     * @test
     */
    public function getDataWithReplacedKeys()
    {
        $dataWithReplacedKeys = $this->validationResponse->getDataWithReplacedKeys();
        static::assertArrayHasKey('json_ld', $dataWithReplacedKeys);
        static::assertEquals('Phoenix', $dataWithReplacedKeys['json_ld'][0]['http___schema_org_name'][0]['_value']);
    }
}
