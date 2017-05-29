<?php

/**
 * @namespace
 */
namespace Codeception\Module\Yandex\StructuredData;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\UriInterface;
use Yandex\Common\AbstractServiceClient;
use Yandex\Common\Exception\ForbiddenException;
use Yandex\Dictionary\Exception\DictionaryException;

/**
 * Class StructuredDataClient
 * @package Codeception\Module\Yandex\StructuredData
 */
class StructuredDataClient extends AbstractServiceClient
{
    /**
     * @var
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $serviceDomain = 'validator-api.semweb.yandex.ru/v1.1';

    /**
     * @param string $apiKey API key
     */
    public function __construct($apiKey)
    {
        $this->setApiKey($apiKey);
    }

    /**
     * @param string $apiKey
     *
     * @return $this
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     *
     * @return string
     */
    protected function getValidateHtmlUrl()
    {
        $resource = 'document_parser';
        $query = http_build_query(
            [
                'apikey' => $this->getApiKey(),
                'id' => md5(uniqid()),
                'lang' => 'en',
                'only_errors' => 'true',
                'pretty' => 'false'
            ]
        );
        $url = $this->getServiceUrl($resource) . '?' . $query;

        return $url;
    }

    /**
     * Validates the $html against the Yandex API
     *
     * @param string $html
     *
     * @return ValidationResponse|boolean
     *
     * @throws ForbiddenException
     */
    public function validateHtml($html)
    {
        $url = $this->getValidateHtmlUrl();
        $response = $this->sendRequest(
            'POST',
            $url,
            [
                'version' => $this->serviceProtocolVersion,
                'body' => $html
            ]
        );

        if ($response->getStatusCode() === 200 || $response->getStatusCode() === 204) {
            return $this->parseValidateResponse($response);
        }
        return false;
    }

    /**
     * @param Response $response
     * @return ValidationResponse
     */
    protected function parseValidateResponse(Response $response)
    {
        $responseData = $response->getBody();
        $responseObject = json_decode($responseData, true);

        $id = isset($responseObject['id']) ? $responseObject['id'] : null;
        $data = isset($responseObject['data']) ? $responseObject['data'] : [];

        $validationResponse = new ValidationResponse(
            $id,
            $data
        );

        return $validationResponse;
    }

    /**
     * Sends a request
     *
     * @param string $method HTTP method
     * @param string|UriInterface $uri URI object or string.
     * @param array $options Request options to apply.
     *
     * @return Response
     *
     * @throws ForbiddenException
     * @throws DictionaryException
     */
    protected function sendRequest($method, $uri, array $options = [])
    {
        try {
            $response = $this->getClient()->request($method, $uri, $options);
        } catch (ClientException $ex) {
            $response = $ex->getResponse();
            $code = $response->getStatusCode();
            $message = $response->getReasonPhrase();

            if ($code === 403) {
                throw new ForbiddenException($message);
            }
        }

        return $response;
    }
}
