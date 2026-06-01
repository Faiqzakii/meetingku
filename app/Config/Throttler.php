<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Throttler extends BaseConfig
{
    /**
     * Named rate limiters.
     * Each key is used as the first argument to the throttler filter.
     *
     * @var array<string, array{rate: int, duration: int}>
     */
    public array $throttlers = [
        'api' => [
            'rate'     => 60,   // 60 requests
            'duration' => 60,   // per 60 seconds (1 minute)
        ],
    ];
}
