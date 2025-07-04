<?php

namespace Ahmedd\ZohoBooks;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class TokenManager
{
    protected $clientId;
    protected $clientSecret;
    protected $refreshToken;
    protected $tokenPath;
    protected $accountsDomain;

    public function __construct($clientId, $clientSecret, $refreshToken, $tokenPath = null, $accountsDomain = 'https://accounts.zoho.eu')
    {
        $this->clientId       = $clientId;
        $this->clientSecret   = $clientSecret;
        $this->refreshToken   = $refreshToken;
        $this->accountsDomain = rtrim($accountsDomain, '/');

        if ($tokenPath === null && function_exists('storage_path')) {
            $appPath = storage_path('app');
            if (!file_exists($appPath)) {
                mkdir($appPath, 0775, true);
            }
            $this->tokenPath = $appPath . '/zoho_token.json';
        } else {
            $this->tokenPath = $tokenPath;
        }
    }

    /**
     * Get access token, refreshing if expired.
     *
     * @return string
     * @throws \Exception
     */
    public function getAccessToken()
    {
        $tokenData = $this->readTokenFile();

        if (!isset($tokenData['access_token']) || $this->isTokenExpired($tokenData)) {
            return $this->refreshTokenAndStore();
        }

        return $tokenData['access_token'];
    }

    /**
     * Refresh Zoho access token and store it with expires_at.
     *
     * @return string
     * @throws \Exception
     */
    public function refreshTokenAndStore()
    {
        $client = new Client();

        try {
            $response = $client->request('POST', $this->accountsDomain . '/oauth/v2/token', [
                'form_params' => array(
                    'refresh_token' => $this->refreshToken,
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type'    => 'refresh_token',
                ),
                'timeout' => 10,
            ]);

            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if (!isset($data['access_token'])) {
                throw new \Exception('Token response did not contain access_token. Response: ' . $body);
            }

            // Add expiration timestamp
            $data['expires_at'] = time() + (isset($data['expires_in']) ? intval($data['expires_in']) : 3600) - 60;

            // Ensure folder exists
            $dir = dirname($this->tokenPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }

            file_put_contents($this->tokenPath, json_encode($data));

            return $data['access_token'];

        } catch (RequestException $e) {
            throw new \Exception('Failed to refresh Zoho token: ' . $e->getMessage());
        }
    }

    /**
     * Read saved token file.
     *
     * @return array
     */
    protected function readTokenFile()
    {
        if (!file_exists($this->tokenPath)) {
            return array();
        }

        $json = file_get_contents($this->tokenPath);
        $data = json_decode($json, true);

        return is_array($data) ? $data : array();
    }

    /**
     * Check if the saved token has expired.
     *
     * @param array $data
     * @return bool
     */
    protected function isTokenExpired($data)
    {
        return !isset($data['expires_at']) || time() >= $data['expires_at'];
    }
}
