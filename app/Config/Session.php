<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Session\Handlers\FileHandler;

class Session extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Session Driver
     * --------------------------------------------------------------------------
     *
     * The session storage driver to use:
     * - `FileHandler` (CodeIgniter's default) uses the file system
     * - `DatabaseHandler` uses a database table
     * - `RedisHandler` uses redis
     * - `MemcachedHandler` uses memcached
     * - `ArrayHandler` uses a PHP array
     *
     * @var string
     */
    public $driver = FileHandler::class;

    /**
     * --------------------------------------------------------------------------
     * Session Cookie Name
     * --------------------------------------------------------------------------
     *
     * The session cookie name, must contain only [0-9a-z_-] characters
     *
     * @var string
     */
    public $cookieName = 'ci_session';

    /**
     * --------------------------------------------------------------------------
     * Session Expiration
     * --------------------------------------------------------------------------
     *
     * The number of SECONDS you want the session to last.
     * Setting to 0 (zero) means expire when the browser is closed.
     *
     * @var int
     */
    public $expiration = 7200;

    /**
     * --------------------------------------------------------------------------
     * Session Save Path
     * --------------------------------------------------------------------------
     *
     * The location to save sessions to.
     *
     * For the 'files' driver, it's a path to a writable directory.
     *
     * @var string
     */
    public $savePath = WRITEPATH . 'session';

    /**
     * --------------------------------------------------------------------------
     * Session Match IP
     * --------------------------------------------------------------------------
     *
     * Whether to match the user's IP address when reading the session data.
     *
     * WARNING: If you're using the database driver, don't forget to update
     * your session table's PRIMARY KEY when changing this setting.
     *
     * @var bool
     */
    public $matchIP = false;

    /**
     * --------------------------------------------------------------------------
     * Session Time to Update
     * --------------------------------------------------------------------------
     *
     * How many seconds between CI regenerating the session ID.
     *
     * @var int
     */
    public $timeToUpdate = 300;

    /**
     * --------------------------------------------------------------------------
     * Session Regenerate Destroy
     * --------------------------------------------------------------------------
     *
     * Whether to destroy session data associated with the old session ID
     * when auto-regenerating the session ID. When set to FALSE, the data
     * will be later deleted by the garbage collector.
     *
     * @var bool
     */
    public $regenerateDestroy = true;

    /**
     * --------------------------------------------------------------------------
     * Session Database Group
     * --------------------------------------------------------------------------
     *
     * DB Group for the database session driver.
     *
     * @var string|null
     */
    public $DBGroup = null;

    /**
     * --------------------------------------------------------------------------
     * Cookie Secure Flag
     * --------------------------------------------------------------------------
     *
     * Whether to set the secure flag on the cookie.
     *
     * @var bool
     */
    public $cookieSecure = false;

    /**
     * --------------------------------------------------------------------------
     * Cookie HTTP Only Flag
     * --------------------------------------------------------------------------
     *
     * Whether to set the HTTP-only flag on the cookie.
     *
     * @var bool
     */
    public $cookieHTTPOnly = true;

    /**
     * --------------------------------------------------------------------------
     * Cookie SameSite Flag
     * --------------------------------------------------------------------------
     *
     * Determines how the cookie is handled when third-party requests are made.
     * See https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie/SameSite
     *
     * @var string 'None'|'Lax'|'Strict'
     */
    public $cookieSameSite = 'Lax';
}
