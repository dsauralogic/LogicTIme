<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ZohoService
{
    protected $clientId;
    protected $clientSecret;
    protected $refreshToken;
    protected $apiDomain;
    protected $portalName;

    public function __construct()
    {
        $this->clientId = config('zoho.client_id');
        $this->clientSecret = config('zoho.client_secret');
        $this->refreshToken = config('zoho.refresh_token');
        $this->apiDomain = config('zoho.api_domain', 'https://projectsapi.zoho.com/restapi');
        $this->portalName = config('zoho.portal_name', 'logicacfi');
    }

    public function getAccessToken()
    {
        return Cache::remember('zoho_access_token', now()->addMinutes(50), function () {
            $response = Http::asForm()->post('https://accounts.zoho.com/oauth/v2/token', [
                'refresh_token' => $this->refreshToken,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'refresh_token',
            ]);

            if (!$response->successful()) {
                Log::error('Error al obtener access_token', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Error al obtener access_token: ' . $response->status());
            }

            return $response->json()['access_token'];
        });
    }

    public function get($endpoint)
    {
        $url = $this->apiDomain . $endpoint;
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)->get($url);

        if (!$response->successful()) {
            Log::error('Error al hacer solicitud GET a Zoho', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Error en solicitud GET a Zoho: ' . $response->status());
        }

        return $response->json();
    }

    public function post($endpoint, $data)
    {
        $url = $this->apiDomain . $endpoint;
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->asForm()
            ->post($url, $data);

        if (!$response->successful()) {
            Log::error('Error al hacer solicitud POST a Zoho', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
                'data' => $data,
            ]);
            throw new \Exception('Error en solicitud POST a Zoho: ' . $response->status());
        }

        return $response->json();
    }

    public function fetchTasks($projectId)
    {
        $endpoint = "/portal/{$this->portalName}/projects/{$projectId}/tasks/";
        $response = $this->get($endpoint);
        return $response['tasks'] ?? [];
    }
}
