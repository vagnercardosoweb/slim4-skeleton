<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 12/11/2023 Vagner Cardoso
 */

use Core\Config;
use Core\Session;
use Core\Support\Asset;
use Core\Support\Env;
use Core\Support\Path;

return [
    'options' => [
        'debug' => 'development' === Env::get('APP_ENV'),
        'charset' => 'UTF-8',
        'strict_variables' => false,
        'autoescape' => 'html',
        'cache' => 'production' === Env::get('APP_ENV') ? Path::storage('/cache/twig') : false,
        'auto_reload' => true,
        'optimizations' => -1,
    ],

    'templates' => [
        '' => Path::resources('/views'),
        'errors' => Path::resources('/errors'),
        'swagger' => Path::resources('/swagger'),
    ],

    'filters' => [
        'is_string' => 'is_string',
    ],

    'functions' => [
        'dd' => 'dd',
    ],

    'globals' => [
        'env' => Env::class,
        'config' => Config::class,
        'session' => Session::class,
        'asset' => Asset::class,
    ],
];
