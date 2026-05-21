<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{
    /**
     * The directory that holds the Migrations and Seeds directories.
     */
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    /**
     * Lets you choose which connection group to use if no other is specified.
     */
    public string $defaultGroup = 'default';

    /**
     * The default database connection.
     *
     * @var array<string, mixed>
     */
    public array $default = [
        'DSN'          => '',
        'hostname'     => 'localhost',
        'username'     => 'root',
        'password'     => '',
        'database'     => 'meetingku',
        'schema'       => 'public',
        'DBDriver'     => 'MySQLi',
        'DBPrefix'     => '',
        'pConnect'     => false,
        'DBDebug'      => true,
        'charset'      => 'utf8mb4',
        'DBCollat'     => 'utf8mb4_general_ci',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'     => [],
        'port'         => 3306,
        'numberNative' => false,
        'foundRows'    => false,
        'dateFormat'   => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    //    /**
    //     * Sample database connection for SQLite3.
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'database'    => 'database.db',
    //        'DBDriver'    => 'SQLite3',
    //        'DBPrefix'    => '',
    //        'DBDebug'     => true,
    //        'swapPre'     => '',
    //        'failover'    => [],
    //        'foreignKeys' => true,
    //        'busyTimeout' => 1000,
    //        'synchronous' => null,
    //        'dateFormat'  => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    //    /**
    //     * Sample database connection for Postgre.
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'DSN'        => '',
    //        'hostname'   => 'localhost',
    //        'username'   => 'root',
    //        'password'   => 'root',
    //        'database'   => 'ci4',
    //        'schema'     => 'public',
    //        'DBDriver'   => 'Postgre',
    //        'DBPrefix'   => '',
    //        'pConnect'   => false,
    //        'DBDebug'    => true,
    //        'charset'    => 'utf8',
    //        'swapPre'    => '',
    //        'failover'   => [],
    //        'port'       => 5432,
    //        'dateFormat' => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    //    /**
    //     * Sample database connection for SQLSRV.
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'DSN'        => '',
    //        'hostname'   => 'localhost',
    //        'username'   => 'root',
    //        'password'   => 'root',
    //        'database'   => 'ci4',
    //        'schema'     => 'dbo',
    //        'DBDriver'   => 'SQLSRV',
    //        'DBPrefix'   => '',
    //        'pConnect'   => false,
    //        'DBDebug'    => true,
    //        'charset'    => 'utf8',
    //        'swapPre'    => '',
    //        'encrypt'    => false,
    //        'failover'   => [],
    //        'port'       => 1433,
    //        'dateFormat' => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    //    /**
    //     * Sample database connection for OCI8.
    //     *
    //     * You may need the following environment variables:
    //     *   NLS_LANG                = 'AMERICAN_AMERICA.UTF8'
    //     *   NLS_DATE_FORMAT         = 'YYYY-MM-DD HH24:MI:SS'
    //     *   NLS_TIMESTAMP_FORMAT    = 'YYYY-MM-DD HH24:MI:SS'
    //     *   NLS_TIMESTAMP_TZ_FORMAT = 'YYYY-MM-DD HH24:MI:SS'
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'DSN'        => 'localhost:1521/XEPDB1',
    //        'username'   => 'root',
    //        'password'   => 'root',
    //        'DBDriver'   => 'OCI8',
    //        'DBPrefix'   => '',
    //        'pConnect'   => false,
    //        'DBDebug'    => true,
    //        'charset'    => 'AL32UTF8',
    //        'swapPre'    => '',
    //        'failover'   => [],
    //        'dateFormat' => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    /**
     * This database connection is used when running PHPUnit database tests.
     *
     * @var array<string, mixed>
     */
    public array $development = [
        'DSN'         => '',
        'hostname'    => '127.0.0.1',
        'username'    => '',
        'password'    => '',
        'database'    => ':memory:',
        'schema'      => 'public',
        'DBDriver'    => 'SQLite3',
        'DBPrefix'    => 'db_',  // Needed to ensure we're working correctly with prefixes live. DO NOT REMOVE FOR CI DEVS
        'pConnect'    => false,
        'DBDebug'     => true,
        'charset'     => 'utf8',
        'DBCollat'    => '',
        'swapPre'     => '',
        'encrypt'     => false,
        'compress'    => false,
        'strictOn'    => false,
        'failover'    => [],
        'port'        => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
        'dateFormat'  => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        // Override defaults using .env values at runtime (avoids non-constant default expressions)
        $this->default['DSN']          = env('database.default.DSN', $this->default['DSN']);
        $this->default['hostname']     = env('database.default.hostname', env('DB_HOST', $this->default['hostname']));
        $this->default['username']     = env('database.default.username', env('DB_USERNAME', $this->default['username']));
        $this->default['password']     = env('database.default.password', env('DB_PASSWORD', $this->default['password']));
        $this->default['database']     = env('database.default.database', env('DB_DATABASE', $this->default['database']));
        $this->default['schema']       = env('database.default.schema', env('DB_SCHEMA', $this->default['schema']));
        $this->default['DBDriver']     = env('database.default.DBDriver', env('DB_DRIVER', $this->default['DBDriver']));
        $this->default['DBPrefix']     = env('database.default.DBPrefix', $this->default['DBPrefix']);
        $this->default['pConnect']     = (bool) env('database.default.pConnect', $this->default['pConnect']);
        $this->default['DBDebug']      = (bool) env('database.default.DBDebug', $this->default['DBDebug']);
        $this->default['charset']      = env('database.default.charset', $this->default['charset']);
        $this->default['DBCollat']     = env('database.default.DBCollat', $this->default['DBCollat']);
        $this->default['swapPre']      = env('database.default.swapPre', $this->default['swapPre']);
        $this->default['encrypt']      = (bool) env('database.default.encrypt', $this->default['encrypt']);
        $this->default['compress']     = (bool) env('database.default.compress', $this->default['compress']);
        $this->default['strictOn']     = (bool) env('database.default.strictOn', $this->default['strictOn']);
        $this->default['failover']     = env('database.default.failover', $this->default['failover']) ?: [];
        $this->default['port']         = (int) env('database.default.port', env('DB_PORT', $this->default['port']));
        $this->default['numberNative'] = (bool) env('database.default.numberNative', $this->default['numberNative']);
        $this->default['foundRows']    = (bool) env('database.default.foundRows', $this->default['foundRows']);

        $this->development['DSN']          = env('database.development.DSN', $this->development['DSN']);
        $this->development['hostname']     = env('database.development.hostname', $this->development['hostname']);
        $this->development['username']     = env('database.development.username', $this->development['username']);
        $this->development['password']     = env('database.development.password', $this->development['password']);
        $this->development['database']     = env('database.development.database', $this->development['database']);
        $this->development['schema']       = env('database.development.schema', $this->development['schema']);
        $this->development['DBDriver']     = env('database.development.DBDriver', $this->development['DBDriver']);
        $this->development['DBPrefix']     = env('database.development.DBPrefix', $this->development['DBPrefix']);
        $this->development['pConnect']     = (bool) env('database.development.pConnect', $this->development['pConnect']);
        $this->development['DBDebug']      = (bool) env('database.development.DBDebug', $this->development['DBDebug']);
        $this->development['charset']      = env('database.development.charset', $this->development['charset']);
        $this->development['DBCollat']     = env('database.development.DBCollat', $this->development['DBCollat']);
        $this->development['swapPre']      = env('database.development.swapPre', $this->development['swapPre']);
        $this->development['encrypt']      = (bool) env('database.development.encrypt', $this->development['encrypt']);
        $this->development['compress']     = (bool) env('database.development.compress', $this->development['compress']);
        $this->development['strictOn']     = (bool) env('database.development.strictOn', $this->development['strictOn']);
        $this->development['failover']     = env('database.development.failover', $this->development['failover']) ?: [];
        $this->development['port']         = (int) env('database.development.port', $this->development['port']);
        $this->development['foreignKeys']  = (bool) env('database.development.foreignKeys', $this->development['foreignKeys']);
        $this->development['busyTimeout']  = (int) env('database.development.busyTimeout', $this->development['busyTimeout']);

        // Ensure that we always set the database group to 'tests' if
        // we are currently running an automated test suite, so that
        // we don't overwrite live data on accident.
        if (ENVIRONMENT === 'development') {
            $this->defaultGroup = 'development';
        }

        $this->defaultGroup = env('database.defaultGroup', $this->defaultGroup);

        if ($this->default['DBDriver'] === 'Postgre') {
            $this->default['charset'] = env('database.default.charset', 'utf8');
            $this->default['DBCollat'] = env('database.default.DBCollat', '');
        }
    }
}
