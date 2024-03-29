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
use Core\Support\Path;

require_once __DIR__.'/../../public_html/index.php';

return [
    'version_order' => 'creation',

    'paths' => [
        'seeds' => Path::resources('/database/seeders'),
        'migrations' => Path::resources('/database/migrations'),
    ],

    'environments' => [
        'default_migration_table' => 'migrations',
        'default_environment' => 'default',

        'sqlite' => [
            'suffix' => '',
            'memory' => 'memory' === Env::get('DB_DATABASE'),
            'name' => Env::get('DB_DATABASE'),
        ],

        'default' => [
            'adapter' => Env::get('DB_DRIVER', 'pgsql'),
            'host' => Env::get('DB_HOST', 'mysql'),
            'port' => Env::get('DB_PORT', '3306'),
            'name' => Env::get('DB_DATABASE', 'development'),
            'user' => Env::get('DB_USERNAME', 'root'),
            'pass' => Env::get('DB_PASSWORD', 'root'),
            'charset' => Env::get('DB_CHARSET', 'utf8mb4'),
            'collation' => Env::get('DB_COLLATE', 'utf8mb4_general_ci'),
            'table_prefix' => false,
            'table_suffix' => false,
        ],
    ],
];
