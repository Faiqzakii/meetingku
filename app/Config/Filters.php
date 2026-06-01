<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseConfig
{
    /**
     * Configures aliases for Filter classes to
     * make reading things nicer and simpler.
     */
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'throttle'      => \App\Filters\ThrottleFilter::class,
        'auth'          => \App\Filters\LoginFilter::class,
        'admin'         => \App\Filters\AdminFilter::class,
        'public'        => \App\Filters\PublicFilter::class,
    ];

    /**
     * List of filter aliases that are always
     * applied before and after every request.
     */
    public array $globals = [
        'before' => [
            'csrf' => ['except' => [
                'auth/logout',
                'api/whatsapp/messages',
                'api/whatsapp/messages/*',
                'api/meetings',
                'api/meetings/*',
                'api/rooms',
            ]],
            'invalidchars',
        ],
        'after' => [
            'toolbar',
            'secureheaders',
        ],
    ];

    /**
     * List of filter aliases that works on a
     * particular HTTP method (GET, POST, etc.).
     */
    public array $methods = [
        'POST' => ['throttle'],
    ];

    /**
     * List of filter aliases that should run on any
     * before or after URI patterns.
     */
    public array $filters = [
        'auth' => [
            'before' => [
                'meeting/*',
                'pegawai/*',
                'ruangan/*',
                'whatsapp/*',
                'whatsapp'
            ],
            'except' => [
                'auth/*',  // Auth routes are handled by public filter
                '/',       // Root path should be public
                'meeting/upcoming'  // Upcoming meetings should be public
            ]
        ],
        'admin' => [
            'before' => [
                'pegawai/*',
                'ruangan/*',
                'whatsapp/*',
                'whatsapp'
            ],
            'except' => [
                'pegawai/profile',
                'ruangan/view/*'
            ]
        ],
        'public' => [
            'before' => [
                'auth/*'
            ],
            'except' => [
                'auth/logout'  // Logout requires auth filter
            ]
        ]
    ];

    /**
     * List of filter aliases that should run on any
     * before or after URI patterns.
     */
    public array $filterSets = [
        'auth-admin' => [
            'auth',
            'admin'
        ]
    ];
}
