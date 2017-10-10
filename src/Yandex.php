<?php

namespace Codeception\Module;

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

use Codeception\Exception\ModuleException;
use Codeception\Lib\Framework;
use Codeception\Lib\InnerBrowser;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Module;
use Codeception\Module\Yandex\StructuredData\StructuredDataClient;
use Codeception\Module\Yandex\StructuredData\ValidationResponse;
use Codeception\TestInterface;
use Codeception\Util\JsonArray;
use PHPUnit\Framework\Assert;

/**
 * Class Yandex
 * @package Codeception\Module
 */
class Yandex extends Module implements DependsOnModule
{

    protected $config = [
        'apiKey' => '',
        'url' => ''
    ];

    protected $dependencyMessage = <<<EOF
Example configuring PhpBrowser as backend for Yandex module.
--
modules:
    enabled:
        - Yandix:
            depends: PhpBrowser
            url: http://localhost/api/
            apiKey: xxxxx-xxxx-xxxx-xxxx-xxxxxxxx
--
EOF;

    /**
     * @var \Symfony\Component\HttpKernel\Client|\Symfony\Component\BrowserKit\Client
     */
    public $client = null;
    public $isFunctional = false;

    /**
     * @var InnerBrowser
     */
    protected $connectionModule;

    /**
     * @var StructuredDataClient
     */
    protected $structuredDataClient;

    /**
     * @var array
     */
    public $params = [];

    /**
     * @var string
     */
    public $response = '';

    /**
     * @param TestInterface $test
     */
    public function _before(TestInterface $test)
    {
        $this->client = &$this->connectionModule->client;
        $this->resetVariables();
    }

    protected function resetVariables()
    {
        $this->params = [];
        $this->response = '';
        $this->connectionModule->headers = [];
    }

    /**
     * @return array
     */
    public function _depends()
    {
        return ['Codeception\Lib\InnerBrowser' => $this->dependencyMessage];
    }

    /**
     * @param InnerBrowser $connection
     */
    public function _inject(InnerBrowser $connection)
    {
        $this->connectionModule = $connection;
        if ($this->connectionModule instanceof Framework) {
            $this->isFunctional = true;
        }
        if ($this->connectionModule instanceof PhpBrowser) {
            if (!$this->connectionModule->_getConfig('url')) {
                $this->connectionModule->_setConfig(['url' => $this->config['url']]);
            }
        }

        if (!isset($this->config['apiKey'])) {
            throw new ModuleException($this, "ApiKey is empty. Please get one from: https://developer.tech.yandex.ru");
        }
        $this->structuredDataClient = new StructuredDataClient($this->config['apiKey']);
    }

    /**
     * @return \Symfony\Component\BrowserKit\Client|\Symfony\Component\HttpKernel\Client
     * @throws ModuleException
     */
    protected function getRunningClient()
    {
        if ($this->client->getInternalRequest() === null) {
            throw new ModuleException($this, "Response is empty. Use `\$I->sendXXX()` methods to send HTTP request");
        }
        return $this->client;
    }

    /**
     * @param StructuredDataClient $structuredDataClient
     */
    public function setStructuredDataClient(StructuredDataClient $structuredDataClient)
    {
        $this->structuredDataClient = $structuredDataClient;
    }

    /**
     * @return string
     */
    protected function getResponseContent()
    {
        return $this->connectionModule->_getResponseContent();
    }

    /**
     * @return array Array of matching items
     * @throws \Exception
     */
    public function grabStructuredDataFromApiResponse()
    {
        $responseContent = $this->getResponseContent();
        $validationResponse = $this->structuredDataClient->validateHtml($responseContent, false);

        return $validationResponse->getData();
    }

    /**
     * @param string $jsonPath
     * @return array Array of matching items
     * @throws \Exception
     */
    public function grabStructuredDataFromApiResponseByJsonPath($jsonPath)
    {
        $responseContent = $this->getResponseContent();
        $validationResponse = $this->structuredDataClient->validateHtml($responseContent, false);

        return (new JsonArray(json_encode($validationResponse->getDataWithReplacedKeys())))
            ->filterByJsonPath($jsonPath);
    }

    /**
     *
     */
    public function seeResponseContainsValidStructuredDataMarkup()
    {
        $responseContent = $this->getResponseContent();
        $validationResponse = $this->structuredDataClient->validateHtml($responseContent);
        Assert::assertTrue(
            $validationResponse->isValid(),
            implode(PHP_EOL, $validationResponse->getErrorsFormatted())
        );
    }

    /**
     *
     */
    public function seeResponseContainsValidJsonLdMarkup()
    {
        $responseContent = $this->getResponseContent();
        $validationResponse = $this->structuredDataClient->validateHtml($responseContent);
        Assert::assertTrue(
            $validationResponse->isValid(ValidationResponse::JSONLD),
            implode(PHP_EOL, $validationResponse->getErrorsFormatted(ValidationResponse::JSONLD))
        );
    }

    /**
     *
     */
    public function seeResponseContainsValidMicrodataMarkup()
    {
        $responseContent = $this->getResponseContent();
        $validationResponse = $this->structuredDataClient->validateHtml($responseContent);
        Assert::assertTrue(
            $validationResponse->isValid(ValidationResponse::MICRODATA),
            implode(PHP_EOL, $validationResponse->getErrorsFormatted(ValidationResponse::MICRODATA))
        );
    }

    /**
     *
     */
    public function seeResponseContainsValidMicroformatMarkup()
    {
        $responseContent = $this->getResponseContent();
        $validationResponse = $this->structuredDataClient->validateHtml($responseContent);
        Assert::assertTrue(
            $validationResponse->isValid(ValidationResponse::MICROFORMAT),
            implode(PHP_EOL, $validationResponse->getErrorsFormatted(ValidationResponse::MICROFORMAT))
        );
    }

    /**
     *
     */
    public function seeResponseContainsValidRdfaMarkup()
    {
        $responseContent = $this->getResponseContent();
        $validationResponse = $this->structuredDataClient->validateHtml($responseContent);
        Assert::assertTrue(
            $validationResponse->isValid(ValidationResponse::RDFA),
            implode(PHP_EOL, $validationResponse->getErrorsFormatted(ValidationResponse::RDFA))
        );
    }
}
