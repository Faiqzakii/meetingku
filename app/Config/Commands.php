<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Commands extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Commands Priority
     * --------------------------------------------------------------------------
     *
     * Here you can specify the command priority. The higher the priority, the
     * sooner the command will be executed.
     *
     * @var array<string, int>
     */
    public array $priority = [];

    /**
     * --------------------------------------------------------------------------
     * Commands
     * --------------------------------------------------------------------------
     *
     * Here you can specify the commands that should be listed when running
     * `php spark list` or any other command that lists available commands.
     *
     * @var array<int, string>
     */
    public array $commands = [
        'auth:create-admin' => \App\Commands\CreateAdmin::class
    ];
}
