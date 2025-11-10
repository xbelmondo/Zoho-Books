<?php

namespace Ahmedd\ZohoBooks;

use GuzzleHttp\Client as BaseClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Ahmedd\ZohoBooks\TokenManager;

class Client
{
    const ENDPOINT = 'https://www.zohoapis.eu/books/v3/';

    /**
     * @var TokenManager
     */
    protected $tokenManager;

    /**
     * @var int
     */
    protected $minDelayMicroseconds = 300000; // 0.3 seconds = ~3 requests per second
    protected $lastRequestTime = 0;

    /**
     * @var Cache // Laravel cache instance
     */
    protected $cache;

    /**
     * @var BaseClient
     */
    protected $httpClient;
    /**
     * @var string
     */
    protected $acessToken;

    /**
     * @var array|string[][]
     */
    protected $lastResponseHeaders = [];


    protected $requestOptions = [];

    /**
     * Client constructor.
     *
     * @param string $acessToken
     * @param ClientInterface|null $httpClient
     * @param array $requestOptions
     */
    public function __construct(TokenManager $authTokenManager, ClientInterface $httpClient = null, array $requestOptions = [])
    {
        $this->tokenManager = $authTokenManager;
        $this->setRequestOauth($this->tokenManager->getAccessToken());

        $this->cache = \Cache::getFacadeRoot(); // Laravel cache instance

        if ($httpClient && $requestOptions) {
            throw new \InvalidArgumentException('If argument 4 is provided, argument 5 must be omitted or passed with an empty array as value');
        }
        $this->requestOptions += ['base_uri' => $this->tokenManager->getBaseApiUrl(), RequestOptions::HTTP_ERRORS => false];
        $this->httpClient = $httpClient ?: new BaseClient($this->requestOptions);
        if (false !== $this->httpClient->getConfig(RequestOptions::HTTP_ERRORS)) {
            throw new \InvalidArgumentException(sprintf('Request option "%s" must be set to `false` at HTTP client', RequestOptions::HTTP_ERRORS));
        }
    }

    public function setThrottleDelayMicroseconds($delay)
    {
        $this->minDelayMicroseconds = $delay;
    }

    protected function throttle()
    {
       // return true;
        $now = microtime(true);
        $elapsed = ($now - $this->lastRequestTime) * 1e6; // microseconds

        if ($elapsed < $this->minDelayMicroseconds) {
            usleep($this->minDelayMicroseconds - $elapsed);
        }

        $this->lastRequestTime = microtime(true);
    }

    protected function cachedGet($cacheKey, $ttlMinutes, callable $callback)
    {
        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $result = $callback();

        $this->cache->put($cacheKey, $result, $ttlMinutes);

        return $result;
    }

    /**
     * append access token to request header
     *
     * @param accessToken string
     * @return void
     */
    private function setRequestOauth($acessToken)
    {
        $this->requestOptions['headers']['Authorization'] = "Zoho-oauthtoken {$acessToken}";
    }

    /**
     * @param string $url
     * @param string $organizationId
     * @param array $filters
     *
     * @return array
     */
    public function getList($url, $organizationId, array $filters)
    {
        $this->throttle();
        $cacheKey = 'zoho:' . md5($url . $organizationId . json_encode($filters));

        return $this->cachedGet($cacheKey, 2, function () use ($url, $organizationId, $filters) {
            return $this->processResult(
                $this->httpClient->get($url, ['query' => array_merge($this->getParams($organizationId), $filters)])
            );
        });
        // return $this->processResult(
        //     $this->httpClient->get($url, ['query' => array_merge($this->getParams($organizationId), $filters)])
        // );
    }

    /**
     * @param string $url
     * @param string $organizationId
     * @param string $id
     * @param array $params Additional query params
     *
     * @return array
     */
    public function get($url, $organizationId, $id, array $params = [])
    {
        $this->throttle();
        if ($id=='pdf') {
          return $this->processResult(
              $this->httpClient->get($url.'/'.$id, ['query' => $params + $this->getParams($organizationId)])
          );
        }
        $cacheKey = 'zoho:' . md5($url . $organizationId . $id . json_encode($params));

        return $this->cachedGet($cacheKey, 2, function () use ($url, $organizationId, $id, $params) {
          return $this->processResult(
              $this->httpClient->get($url.'/'.$id, ['query' => $params + $this->getParams($organizationId)])
          );
        });

    }

    /**
     * @param string $url
     * @param string $organizationId
     * @param array $data
     * @param array $params Additional query params
     *
     * @return array
     */
    public function post($url, $organizationId, array $data = [], array $params = [])
    {
        $this->throttle();
        $body = [
            'query' => $params + $this->getParams($organizationId),
        ];
        if ($data) {
            $body['form_params'] = ['JSONString' => \GuzzleHttp\json_encode($data)];
        }
        return $this->processResult($this->httpClient->post($url, $body));
    }

    /**
     * @param string $url
     * @param string $organizationId
     * @param mixed $id
     * @param array $data
     * @param array $params Additional query params
     *
     * @return array
     */
    public function put($url, $organizationId, $id, array $data = [], array $params = [])
    {
        $this->throttle();
        return $this->processResult($this->httpClient->put(
            $url.'/'.$id,
            [
                'query' => $params + $this->getParams($organizationId),
                'form_params' => ['JSONString' => \GuzzleHttp\json_encode($data)],
            ]
        ));
    }

    /**
     * @param string $url
     * @param string $organizationId
     * @param string $id
     *
     * @return array
     */
    public function delete($url, $organizationId, $id)
    {
        $this->throttle();
        return $this->processResult(
            $this->httpClient->delete($url.'/'.$id, ['query' => $this->getParams($organizationId)])
        );
    }

    /**
     * @param string $organizationId
     * @param array $data
     *
     * @return array
     */
    protected function getParams($organizationId, array $data = [])
    {
        $params = [
            'organization_id' => $organizationId,
        ];
        if ($data) {
            $params['JSONString'] = \GuzzleHttp\json_encode($data);
        }

        return $params;
    }

    /**
     * @param ResponseInterface $response
     *
     * @throws Exception
     *
     * @return array
     */
    protected function processResult(ResponseInterface $response)
    {

        $this->lastResponseHeaders = $response->getHeaders();

        if ($response->getStatusCode() == 429) { //429 Too Many Requests
            $retryAfter = (int)($response->getHeaderLine('Retry-After') ?: 1);
            sleep($retryAfter);
            throw new Exception('ZOHO Rate limit hit. Retry after '.$retryAfter.' sec.');
        }

        try {
            if (preg_grep('/json/', $response->getHeader('Content-Type'))) {
                $result = \GuzzleHttp\json_decode($response->getBody(), true);
            } else {
                return $response->getBody();
            }
        } catch (\InvalidArgumentException $e) {
            $result = [
                'message' => 'Internal API error: '.$response->getStatusCode().' '.$response->getReasonPhrase(),
            ];
        }
        if (isset($result['code']) && 0 == $result['code']) {
            return $result;
        }
        throw new Exception('Response from Zoho is not success. Message: '.$result['message']);
    }
}
