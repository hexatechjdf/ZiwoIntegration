<?php

namespace App\Services;

use App\Models\ZiwoDetail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\ZiwoTokenResource;

class ZiwoService
{
    protected $cacheTTL = 3600; // Cache time-to-live in seconds

    public function getToken($companyId, $locationId = null)
    {
        $cacheKey = $this->getCacheKey($locationId);

        // Check cache first
        if (Cache::has($cacheKey)) {
            $credentials = $this->getCredentials($locationId);
            if ($credentials) {
                $credentials->setAttribute('token', Cache::get($cacheKey));

                // Get the CRM location access token and attach it to the credentials
                if ($locationId) {
                    $crmToken = \CRM::getLocationAccessToken($companyId, $locationId);
                    $credentials->setAttribute('location_token', $crmToken);
                }

                return new ZiwoTokenResource($credentials);
            }
            return null;
        }

        // If no cache, fetch token and cache it
        return $this->fetchAndCacheToken($companyId, $locationId);
    }

    protected function fetchAndCacheToken($companyId, $locationId)
    {
        $credentials = $this->getCredentials($locationId);
        if (!$credentials) {
            return null; // No credentials found
        }

        $response = Http::asForm()->post($credentials->endpoint, [
            'username' => $credentials->username,
            'password' => $credentials->password,
            'remember' => true,
        ]);

        if ($response->successful()) {
            $token = $response->json()['content']['access_token'];
            $cacheKey = $this->getCacheKey($locationId);
            Cache::put($cacheKey, $token, $this->cacheTTL);
            $credentials->setAttribute('token', $token);

            // Attach CRM location token if location_id exists
            if ($locationId) {
                $crmToken = \CRM::getLocationAccessToken($companyId, $locationId);
                $credentials->setAttribute('location_token', $crmToken);
            }

            return new ZiwoTokenResource($credentials);
        }

        return null;
    }

    protected function getCredentials($locationId)
    {
        return ZiwoDetail::where('location_id', $locationId)->first()
            ?? ZiwoDetail::where('location_id', null)->first();
    }

    protected function getCacheKey($locationId)
    {
        return $locationId ? 'ziwo_token_' . $locationId : 'agency_token';
    }
}


?>