<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 01/03/2021 Vagner Cardoso
 */

use Core\Bootstrap;
use Core\Support\Env;

return [
    'name' => Env::get('APP_NAME', 'Slim 4 Skeleton'),
    'version' => Bootstrap::VERSION,
];
