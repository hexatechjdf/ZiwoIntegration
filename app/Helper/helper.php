<?php

use App\Models\Setting;
use App\Models\SpotifyAuth;
use App\Models\SpotifyUserLocation;
use App\Models\User;

function supersetting($key, $default = '')
{
    $setting = Setting::where(['user_id' => 1, 'key' => $key])->first();
    $value = $setting->value ?? $default;
    return $value;
}

function supersetting_old($key, $default = '')
{

        $setting =DB::table('settings')->where(['user_id' => 2, 'key' => $key])->first();
        if ($setting) {
            return $setting->value;
        }
          return $setting;
}

function braceParser($value){
   return str_replace(['[',']'],['{','}'],$value);
}

function loginUser(){
    return auth()->user();
}
function get_option($user_id, $key)
{
    $setting = Setting::where('user_id',$user_id)->where('key',$key)->first();
    if($setting)
    {
        return $setting->value;
    }
    return $setting;
}
function save_settings($key, $value = '', $user_id = null)
{

    $setting = Setting::updateOrCreate(
        ['key' => $key],
        [
            'value' => $value,
            'user_id' => $user_id,
            'key' => $key,
        ]
    );
    \gCache::put($key, $value);
    return $setting;
}

function handleRefresh($loc_id, $userId, $token_user = null)
{

    $refresh_token = $token_user->refresh_token ?? get_setting($userId, 'refresh_token');
    $timeout = 70;
    $lock = Cache::lock('refresh_' . $loc_id, $timeout);
    return $lock->block($timeout, function () use ($refresh_token, $loc_id, $userId, $token_user) {
        $token_user->refresh();
        $current_refresh_token = $token_user->refresh_token ?? get_setting($userId, 'refresh_token');
        if ($refresh_token != $current_refresh_token) {
            return $token_user;
        } else {
            return refreshToken($loc_id, $userId, $current_refresh_token);
        }
    });
}

function handleSpotifyRefresh($loc_id, $userId, $token_user = null)
{

    $refresh_token = $token_user->refresh_token ?? get_setting($userId, 'refresh_token');
    $timeout = 30;
    $lock = Cache::lock('spotify_refresh_' . $loc_id, $timeout);
    return $lock->block($timeout, function () use ($refresh_token, $loc_id, $userId, $token_user) {
        $token_user->refresh();
        $current_refresh_token = $token_user->refresh_token ?? get_setting($userId, 'refresh_token');
        if ($refresh_token != $current_refresh_token) {
            return $token_user;
        } else {
            return refreshToken($loc_id, $userId, $current_refresh_token);
        }
    });
}

function spotifyToken($code, $method = '')
{

    $data = [
        'redirect_uri' => route('spotify.callback'),
        'client_id' => supersetting('spotify_client_id'),
        'client_secret' => supersetting('spotify_client_secret'),
    ];
    $md = empty($method) ? 'code' : 'refresh_token';
    $data[$md] = $code;
    $data['grant_type'] = empty($method) ? 'authorization_code' : 'refresh_token';
    $response = Http::asForm()->post('https://accounts.spotify.com/api/token', $data);
    return $response->json() ?? null;
}

function saveSpotify($data, $token = null, $user_location_id = '')
{
    $is_save = false;
    $accessToken = $data['access_token']??'';
    if(empty($accessToken)){
        $message = $data['error_description']??'Invalid request';
        return [$is_save, $message];
    }
    $refreshToken = $data['refresh_token'] ?? '';
    $expiresIn = $data['expires_in'];
    $type = 'user';
    $loc = null;
    $userId = null;
    if (Auth::check()) {
        $type = 'location';
        $userId = Auth::id();
    }


    if ($token) {
        $token->spotify_access_token = $accessToken;
        if (!empty($refreshToken)) {
            $token->spotify_refresh_token = $refreshToken;
        }

        $token->save();
        try {
            if (!is_null($token->user_id)) {
                $token->refresh();
                \gCache::put(SpotifyAuth::$cacheKey . $token->user_id, $token);
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
        $is_save = true;

    } else {
        $spotifyData = [
            'spotify_access_token' => $accessToken,
            'spotify_refresh_token' => $refreshToken,
            'user_type' => $type,
            'expire_at' => now()->addSeconds($expiresIn),
            'location_id' => $userId,
        ];
        $profileResponse = spotifyApiCall(null, $accessToken, 'me');
        $profileData = null;
        if (is_object($profileResponse)) {
            $profileData = (array) $profileResponse;
        }

        if ($profileData) {
            $spotifyid = $profileData['id'] ?? null;
            $spotifyData = array_merge($spotifyData, ['spotify_auth_id' => $spotifyid,
                'name' => $profileData['display_name'] ?? '',
                'email' => $profileData['email'] ?? '',
            ]);

            $where = [];
            if (is_null($userId)) {
                $where['spotify_auth_id'] = $spotifyid;

            } else {
                $where['user_id'] = $userId;
            }
            $where['user_type'] = $type;
            $token = SpotifyAuth::updateOrCreate(
                $where,
                $spotifyData
            );
            if ($token && !empty($user_location_id)) {
                $user = User::where('location_id', $user_location_id)->first();
                if ($user) {
                    $data = ['spotify_id' => $token->id, 'location_id' => $user->id];
                    SpotifyUserLocation::updateOrCreate($data);
                }
            }
            $is_save = true;
        }
    }
    return [$is_save, $token];

}

function spotifyApiCall($mainId, $token = null, $url = '', $method = 'get', $data = '', $return = 'object', $retries = 0)
{
    $baseUrl = 'https://api.spotify.com/v1/';

    if (!$token) {
        $token = SpotifyAuth::where(['user_id' => $mainId, 'user_type' => 'location'])->orWhere(['spotify_auth_id' => $mainId, 'user_type' => 'user'])->first();
    }

    if (is_string($token)) {
        $accessToken = $token;
    } else {
        $accessToken = $token->spotify_access_token ?? null;
    }
    if (!$accessToken) {
        return 'No Token';
    }
    $endpoint = ltrim($url, '/');
    $hitUrl = $baseUrl . $endpoint;
    $response = null;
    $body = '';
    $resp = '';
    $json = '';
    try {
        $request = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ]);
        if ($method == 'get') {
            $response = $request->get($hitUrl);
        }
        if ($method == 'post') {
            $response = $request->post($hitUrl, $data);
        }
        if ($method == 'put') {
            $response = $request->put($hitUrl, $data);
        }
        if ($method == 'delete') {
            $response = $request->delete($hitUrl);
        }

        $body = $response->body() ?? '';
        $resp = $response->object() ?? null;
        $json = $response->json() ?? null;

        if ($resp && property_exists($resp, 'error')) {
            $resp = $resp->error;
            $status = ($resp->status ?? '');
            $message = $resp->message ?? 'Invalid request';
            if ($status == 401 && $retries == 0 && $token instanceof SpotifyAuth) {
                $data = spotifyToken($token->spotify_refresh_token, 'ref');
                if ($data && isset($data['access_token'])) {
                    list($is_save, $token) = saveSpotify($data, $token);
                    if ($is_save) {
                        return spotifyApiCall($mainId, $token, $url, $method, $data, $return, $retries + 1);
                    }
                }
            }
        }
    } catch (\Throwable $th) {
        //throw $th;
    }
    if ($return == 'object') {
        return $resp;
    }
    if ($return == 'json') {
        return $json;
    }
    if ($return == 'body') {
        return $body;
    }
}
