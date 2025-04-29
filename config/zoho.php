<?php

return [
    'client_id' => env('ZOHO_CLIENT_ID'),
    'client_secret' => env('ZOHO_CLIENT_SECRET'),
    'refresh_token' => env('ZOHO_REFRESH_TOKEN'),
    'api_domain' => env('ZOHO_API_DOMAIN', 'https://projectsapi.zoho.com/restapi'),
    'portal_name' => env('ZOHO_PORTAL_NAME', 'logicacfi'),
];