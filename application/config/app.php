<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 05/11/2023 Vagner Cardoso
 */

use Core\Application;
use Core\Support\Env;

return [
    'name' => Env::get('APP_NAME', 'Slim 4 Skeleton'),
    'version' => Application::VERSION,

    'isTesting' => 'testing' === Env::get('APP_ENV'),
    'isProduction' => 'production' === Env::get('APP_ENV'),
    'isDevelopment' => 'development' === Env::get('APP_ENV'),
];
