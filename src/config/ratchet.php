<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel Ratchet Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can define the default settings for Laravel Ratchet.
    |
     */

    'class' => \Prosystemsc\LaravelRatchet\Examples\Pusher::class,
    'host' => 'localhost',
    'port' => '8080',
    'blackList' => [],
    'driver' => 'IoServer',
    'periodsync' => 2,
];
