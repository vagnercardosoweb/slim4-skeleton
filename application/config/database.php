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
    // Default connection driver
    'default' => Env::get('DB_DRIVER', 'postgres'),

    // Defines the types of connections that will be accepted
    'connections' => [
        'sqlite' => [
            'url' => null,
            'driver' => 'sqlite',
            'database' => Env::get('DB_DATABASE', 'memory'), // Location database sqlite (memory or path)
            'options' => [], // Use pdo connection options \PDO::ATTR... => \PDO::...
            'attributes' => [], // Use pdo->setAttribute(key => value)
            'commands' => [], // Use pdo->exec(...command...)
            'events' => [], // Register Class::class instance of ConnectionEvent
        ],

        'mysql' => [
            'url' => null,
            'driver' => 'mysql',
            'host' => Env::get('DB_HOST', 'localhost'),
            'port' => Env::get('DB_PORT', 3306),
            'username' => Env::get('DB_USERNAME'),
            'password' => Env::get('DB_PASSWORD'),
            'notPassword' => false,
            'database' => Env::get('DB_DATABASE'),
            'charset' => Env::get('DB_CHARSET', 'utf8mb4'),
            'collation' => Env::get('DB_COLLATE', 'utf8mb4_unicode_ci'),
            'timezone' => Env::get('DB_TIMEZONE', 'UTC'),
            'application_name' => Env::get('DB_APPLICATION_NAME', 'app'),
            'options' => [], // Use pdo connection options \PDO::ATTR... => \PDO::...
            'attributes' => [], // Use pdo->setAttribute(key => value)
            'commands' => [], // Use pdo->exec(...command...)
            'events' => [], // Register Class::class instance of ConnectionEvent
        ],

        'pgsql' => [
            'url' => null,
            'driver' => 'pgsql',
            'host' => Env::get('DB_HOST', 'localhost'),
            'port' => Env::get('DB_PORT', 5432),
            'username' => Env::get('DB_USERNAME'),
            'password' => Env::get('DB_PASSWORD'),
            'database' => Env::get('DB_DATABASE'),
            'schema' => Env::get('DB_SCHEMA', 'public'),
            'charset' => Env::get('DB_CHARSET', 'utf8'),
            'timezone' => Env::get('DB_TIMEZONE', 'UTC'),
            'application_name' => Env::get('DB_APPLICATION_NAME', 'app'),
            'options' => [], // Use pdo connection options \PDO::ATTR... => \PDO::...
            'attributes' => [], // Use pdo->setAttribute(key => value)
            'commands' => [], // Use pdo->exec(...command...)
            'events' => [], // Register Class::class instance of ConnectionEvent
        ],

        'sqlsrv' => [
            'url' => null,
            'driver' => 'sqlsrv',
            'host' => Env::get('DB_HOST', '127.0.0.1'),
            'port' => Env::get('DB_PORT', 1433),
            'username' => Env::get('DB_USERNAME'),
            'password' => Env::get('DB_PASSWORD'),
            'database' => Env::get('DB_DATABASE'),
            'charset' => Env::get('DB_CHARSET', 'utf8'),
            'application_name' => Env::get('DB_APPLICATION_NAME', 'app'),
            'options' => [], // Use pdo connection options \PDO::ATTR... => \PDO::...
            'attributes' => [], // Use pdo->setAttribute(key => value)
            'commands' => [], // Use pdo->exec(...command...)
            'events' => [], // Register Class::class instance of ConnectionEvent
        ],
    ],
];
