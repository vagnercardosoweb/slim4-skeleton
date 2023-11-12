<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 12/11/2023 Vagner Cardoso
 */

use Core\Support\Env;

return [
    /*
     * @se https://github.com/nrk/predis/wiki/Connection-Parameters
     *
     * Any configuration you add here following the same parameter name will be valid
     */

    /*
     * Observation:
     *
     * If the [url] variable is set, the other variables will not be used
     */

    'url' => Env::get('REDIS_URL'),

    'host' => Env::get('REDIS_HOST', '127.0.0.1'),
    'port' => Env::get('REDIS_PORT', 6379),
    'prefix' => Env::get('REDIS_PREFIX', 'app'),
    'password' => Env::get('REDIS_PASSWORD'),
    'database' => Env::get('REDIS_DATABASE', '0'),

    /*
     * Specifies the protocol used to communicate with an instance of Redis.
     * Internally the client uses the connection class associated
     * to the specified connection scheme.
     *
     * By default Predis supports tcp (TCP/IP), unix
     * (UNIX domain sockets) or http (HTTP protocol through Webdis).
     */

    'schema' => Env::get('REDIS_SCHEMA', 'tcp'),

    /*
     * Path of the UNIX domain socket file used when
     * connecting to Redis using UNIX domain sockets.
     */

    'path' => Env::get('REDIS_UNIX_PATH'),

    /*
     * Specifies if the underlying connection resource should be
     * left open when a script ends its lifecycle.
     */

    'persistent' => Env::get('REDIS_PERSISTENT', false),

    /*
     * Timeout (expressed in seconds) used when performing read or write operations on
     * the underlying network resource after which an exception is thrown.
     * The default value actually depends on the underlying platform but usually
     * it is 60 seconds.
     */

    'read_write_timeout' => Env::get('REDIS_READ_WRITE_TIMEOUT', 60),
];
