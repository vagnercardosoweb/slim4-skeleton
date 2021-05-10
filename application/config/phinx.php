<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 10/05/2021 Vagner Cardoso
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
        'default_environment' => Env::get('DB_DRIVER', 'mysql'),

        'mysql' => [
            'adapter' => 'mysql',
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
