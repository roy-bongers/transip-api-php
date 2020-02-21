<?php

namespace Transip\Api\Library\HttpClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use Transip\Api\Library\Exception\ApiException;
use Transip\Api\Library\Exception\HttpClientException;
use Transip\Api\Library\Exception\HttpRequestException;
use Transip\Api\Library\Exception\HttpBadResponseException;
use Exception;

class GuzzleClient extends HttpClient implements HttpClientInterface
{
    /**
     * @var Client $client
     */
    private $client;

    public function __construct(string $endpoint)
    {
        $this->client = new Client();
        parent::__construct($this, $endpoint);
    }

    public function setToken(string $token): void
    {
        $config = [
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'User-Agent' => self::USER_AGENT
            ]
        ];
        $this->client = new Client($config);
        $this->token = $token;
    }

    public function get(string $url, array $query = []): array
    {
        $options = [];
        if (count($query) > 0) {
            $options['query'] = $query;
        }

        $this->checkAndRenewToken();
        $options = $this->checkAndSetTestModeToOptions($options);

        try {
            $response = $this->client->get("{$this->endpoint}{$url}", $options);
        } catch (Exception $exception) {
            $this->exceptionHandler($exception);
        }

        if ($response->getStatusCode() !== 200) {
            throw ApiException::unexpectedStatusCode($response);
        }

        if ($response->getBody() == null) {
            throw ApiException::emptyResponse($response);
        }

        $responseBody = json_decode($response->getBody(), true);

        if ($responseBody === null) {
            throw ApiException::malformedJsonResponse($response);
        }

        return $responseBody;
    }

    public function post(string $url, array $body = []): void
    {
        $options['body'] = json_encode($body);

        $this->checkAndRenewToken();
        $options = $this->checkAndSetTestModeToOptions($options);

        try {
            $response = $this->client->post("{$this->endpoint}{$url}", $options);
        } catch (Exception $exception) {
            $this->exceptionHandler($exception);
        }

        if ($response->getStatusCode() !== 201) {
            throw ApiException::unexpectedStatusCode($response);
        }
    }

    public function postAuthentication(string $url, string $signature, array $body): array
    {
        $options['headers'] = ['Signature' => $signature];
        $options['body']    = json_encode($body);

        try {
            $response = $this->client->post("{$this->endpoint}{$url}", $options);
        } catch (Exception $exception) {
            $this->exceptionHandler($exception);
        }

        if ($response->getStatusCode() !== 201) {
            throw ApiException::unexpectedStatusCode($response);
        }

        if ($response->getBody() == null) {
            throw ApiException::emptyResponse($response);
        }

        $responseBody = json_decode($response->getBody(), true);

        if ($responseBody === null) {
            throw ApiException::malformedJsonResponse($response);
        }

        return $responseBody;
    }

    public function put(string $url, array $body): void
    {
        $options['body'] = json_encode($body);

        $this->checkAndRenewToken();
        $options = $this->checkAndSetTestModeToOptions($options);

        try {
            $response = $this->client->put("{$this->endpoint}{$url}", $options);
        } catch (Exception $exception) {
            $this->exceptionHandler($exception);
        }

        if ($response->getStatusCode() !== 204) {
            throw ApiException::unexpectedStatusCode($response);
        }
    }

    public function patch(string $url, array $body): void
    {
        $options['body'] = json_encode($body);

        $this->checkAndRenewToken();
        $options = $this->checkAndSetTestModeToOptions($options);

        try {
            $response = $this->client->patch("{$this->endpoint}{$url}", $options);
        } catch (Exception $exception) {
            $this->exceptionHandler($exception);
        }

        if ($response->getStatusCode() !== 204) {
            throw ApiException::unexpectedStatusCode($response);
        }
    }

    public function delete(string $url, array $body = []): void
    {
        $options['body'] = json_encode($body);

        $this->checkAndRenewToken();
        $options = $this->checkAndSetTestModeToOptions($options);

        try {
            $response = $this->client->delete("{$this->endpoint}{$url}", $options);
        } catch (Exception $exception) {
            $this->exceptionHandler($exception);
        }

        if ($response->getStatusCode() !== 204) {
            throw ApiException::unexpectedStatusCode($response);
        }
    }

    private function exceptionHandler(Exception $exception): void
    {
        try {
            throw $exception;
        } catch (BadResponseException $badResponseException) {
            if ($badResponseException->hasResponse()) {
                throw HttpBadResponseException::badResponseException($badResponseException, $badResponseException->getResponse());
            }
            // Guzzle misclassifies curl exception as a client exception (so there is no response)
            throw HttpClientException::genericRequestException($badResponseException);
        } catch (RequestException $requestException) {
            throw HttpRequestException::requestException($requestException);
        } catch (Exception $exception) {
            throw HttpClientException::genericRequestException($exception);
        }
    }

    private function checkAndSetTestModeToOptions(array $options): array
    {
        if ($this->testMode == false) {
            return $options;
        }

        if (!array_key_exists('query', $options)) {
            $options['query'] = [];
        }

        $options['query']['test'] = 1;

        return $options;
    }
}
