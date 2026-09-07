<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Livestorm API Token
    |--------------------------------------------------------------------------
    |
    | The Bearer token used to authenticate every request. Find yours at:
    | https://app.livestorm.co/settings/integrations/api
    |
    */
    'api_token' => env('LIVESTORM_API_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    */
    'base_url' => env('LIVESTORM_BASE_URL', 'https://api.livestorm.co/v1'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The number of seconds to wait for a response before giving up.
    |
    */
    'timeout' => (int) env('LIVESTORM_TIMEOUT', 8),
];
