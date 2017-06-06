<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleException;
use Codeception\Lib\Framework;
use Codeception\Lib\InnerBrowser;
use Codeception\Lib\Interfaces\API;
use Codeception\Lib\Interfaces\ConflictsWithModule;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Module;
use Codeception\Module\Yandex\StructuredData\ValidationResponse;
use Codeception\TestInterface;
use Codeception\Module\Yandex\StructuredData\StructuredDataClient;
use Codeception\Util\JsonArray;

/**
 * Class Yandex
 * @package Codeception\Module
 */
class Yandex extends Module implements DependsOnModule, API
{

    protected $config = [
        'apiKey' => '',
        'url' => ''
    ];

    protected $dependencyMessage = <<<EOF
Example configuring PhpBrowser as backend for Yandix module.
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

    public $params = [];
    public $response = "";

    public function _before(TestInterface $test)
    {
        $this->client = &$this->connectionModule->client;
        $this->resetVariables();
    }

    protected function resetVariables()
    {
        $this->params = [];
        $this->response = "";
        $this->connectionModule->headers = [];
    }

    public function _depends()
    {
        return ['Codeception\Lib\InnerBrowser' => $this->dependencyMessage];
    }

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

    protected function getRunningClient()
    {
        if ($this->client->getInternalRequest() === null) {
            throw new ModuleException($this, "Response is empty. Use `\$I->sendXXX()` methods to send HTTP request");
        }
        return $this->client;
    }

    /**
     * @return array Array of matching items
     * @throws \Exception
     */
    public function grabStructuredDataFromApiResponse()
    {
        $responseContent = $this->connectionModule->_getResponseContent();
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
        $responseContent = $this->connectionModule->_getResponseContent();
        $validationResponse = $this->structuredDataClient->validateHtml($responseContent, false);

        return (new JsonArray(json_encode($validationResponse->getDataWithReplacedKeys())))
            ->filterByJsonPath($jsonPath);
    }

    /**
     * @return bool
     */
    public function seeResponseContainsValidStructuredDataMarkup()
    {
        $responseContent = $this->connectionModule->_getResponseContent();
        $validationResponse = $this->structuredDataClient->validateHtml($responseContent);
        \PHPUnit_Framework_Assert::assertTrue(
            $validationResponse->isValid(),
            implode(PHP_EOL, $validationResponse->getErrorsFormatted())
        );
    }

    /**
     * @return bool
     */
    public function seeResponseContainsValidJsonLdMarkup()
    {
        $responseContent = $this->connectionModule->_getResponseContent();
        $validationResponse = $this->structuredDataClient->validateHtml($responseContent);
        \PHPUnit_Framework_Assert::assertTrue(
            $validationResponse->isValid(ValidationResponse::JSONLD),
            implode(PHP_EOL, $validationResponse->getErrorsFormatted(ValidationResponse::JSONLD))
        );
    }

    /**
     * @return bool
     */
    public function seeResponseContainsValidMicrodataMarkup()
    {
        $responseContent = $this->connectionModule->_getResponseContent();
        $validationResponse = $this->structuredDataClient->validateHtml($responseContent);
        \PHPUnit_Framework_Assert::assertTrue(
            $validationResponse->isValid(ValidationResponse::MICRODATA),
            implode(PHP_EOL, $validationResponse->getErrorsFormatted(ValidationResponse::MICRODATA))
        );
    }

    /**
     * @return bool
     */
    public function seeResponseContainsValidMicroformatMarkup()
    {
        $responseContent = $this->connectionModule->_getResponseContent();
        $validationResponse = $this->structuredDataClient->validateHtml($responseContent);
        \PHPUnit_Framework_Assert::assertTrue(
            $validationResponse->isValid(ValidationResponse::MICROFORMAT),
            implode(PHP_EOL, $validationResponse->getErrorsFormatted(ValidationResponse::MICROFORMAT))
        );
    }

    /**
     * @return bool
     */
    public function seeResponseContainsValidRdfaMarkup()
    {
        $responseContent = $this->connectionModule->_getResponseContent();
        $validationResponse = $this->structuredDataClient->validateHtml($responseContent);
        \PHPUnit_Framework_Assert::assertTrue(
            $validationResponse->isValid(ValidationResponse::RDFA),
            implode(PHP_EOL, $validationResponse->getErrorsFormatted(ValidationResponse::RDFA))
        );
    }
}
