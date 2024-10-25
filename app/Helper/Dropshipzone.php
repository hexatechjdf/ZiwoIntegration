<?php

namespace App\Helper;

use Carbon\Carbon;

class Dropshipzone
{
    protected static $base_url = 'https://api.dropshipzone.com.au';
    protected static $prefix = '/v2/';
    public static function makeRequest($user, $method, $url, $data = [], $headers = [], $is_full_url = false, $reauth = false)
    {

        $access_token = '';
        if ($user && !$reauth) {
            $access_token = $user->dropshipzonetoken?->access_token;
            // dd($access_token,$user->dropshipzonetoken,$user->id);
        }
        $url1 = '';
        if (!$is_full_url) {
            $url1 = static::$base_url;
            if (strpos($url, '/rp/') === false) {
                $url1 .= static::$prefix;
            }
        }
        $url1 .= $url;



        $ch = curl_init();

        // Set cURL options based on the request method
        curl_setopt($ch, CURLOPT_URL, $url1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $method = strtoupper($method);
        if ($method == 'GET') {
            if (!empty($data)) {
                $url1 .= '?' . http_build_query($data);
                curl_setopt($ch, CURLOPT_URL, $url1);
            }
        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        // Set headers
        $defaultHeaders = [
            'Content-Type: application/json',
            'Accept: application/json',
        ];
        if (!empty($access_token)) {
            $defaultHeaders[] = 'Authorization: jwt ' . $access_token;
        }

        $allHeaders = array_merge($defaultHeaders, $headers);
        // dd($allHeaders);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);

        // Execute cURL request
        $response1 = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['error' => $error];
        }
        curl_close($ch);

        $response = json_decode($response1, true);
        $isCode = $response['code'] ?? '';
        if (!empty($isCode) && $isCode == 'Unauthorized') {
            $isUpdated = static::ensureValidDropshipzoneToken($user, true);
            if ($isUpdated) {
                return static::makeRequest($user, $method, $url, $data, $headers, false, $reauth);
            }
        }
        // Close cURL session

        // Decode response
        return $response1;
    }
    public static function ensureValidDropshipzoneToken($user, $force_update_token = false)
    {
        $update_token = true;
        $isUpdated = false;
        if ($user->dropshipzonetoken && !$force_update_token) {
            $expDateTime = Carbon::parse($user->dropshipzonetoken->exp_date_time);
            if ($expDateTime->isFuture()) {
                $update_token = false;
            }
        }
        if ($update_token || $force_update_token) {
            $data = [
                'email' => get_option($user->id, "email"),
                'password' => get_option($user->id, "password"),
                "ssoEnabled" => true,
                "isVerifyRecaptcha" => false,
                "redirect_url" => "https://www.dropshipzone.com.au/customer/account/afterLogin/",
            ];
            $response = Dropshipzone::makeRequest($user, 'POST', 'https://retail.dropshipzone.com.au/api/verify', $data, [], true, true);
            $data = json_decode($response, true);
            if (!empty($data['status'])) {
                $isUpdated = true;
                \app\Models\DropshipzoneToken::setAccessToken($user->id, $data);
            }
        }
        return $isUpdated;
    }
}
