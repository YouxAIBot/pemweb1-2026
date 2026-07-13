<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'responses_endpoint' => env('OPENAI_RESPONSES_ENDPOINT', 'https://api.openai.com/v1/responses'),
        'model' => env('OPENAI_QUESTION_MODEL', 'gpt-4.1-mini'),
    ],


    'google_tts' => [
        'api_key' => env('GOOGLE_TTS_API_KEY'),
        'endpoint' => env('GOOGLE_TTS_ENDPOINT', 'https://texttospeech.googleapis.com/v1/text:synthesize'),
        'default_language' => env('GOOGLE_TTS_DEFAULT_LANGUAGE', 'en-US'),
        'default_voice' => env('GOOGLE_TTS_DEFAULT_VOICE', 'en-US-Neural2-C'),
    ],

    'deepl' => [
        'api_key' => env('DEEPL_API_KEY'),
        'endpoint' => env('DEEPL_ENDPOINT', 'https://api-free.deepl.com/v2/translate'),
        'default_target_lang' => env('DEEPL_DEFAULT_TARGET_LANG', 'EN'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
