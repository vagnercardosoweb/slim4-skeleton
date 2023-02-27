<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 27/02/2023 Vagner Cardoso
 */

use Core\Support\Env;
use Core\Support\Path;

$configRedis = require_once __DIR__.'/redis.php';

return [
    /*
     * Default cache store
     *
     * Supported: "redis", "file"
     */

    'default' => Env::get('CACHE_DRIVER', 'redis'),

    'drivers' => [
        'file' => [
            'path' => Path::storage('/cache/server'),
            'permission' => 0755,
        ],

        'redis' => array_merge($configRedis, [
            'prefix' => Env::get('REDIS_PREFIX_CACHE', 'cache:'),
            'database' => Env::get('REDIS_DATABASE_CACHE', '1'),
        ]),
    ],
];
