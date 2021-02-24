<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 24/02/2021 Vagner Cardoso
 */

use App\Middlewares\CorsMiddleware;
use Core\Route;

Route::group([
    'pattern' => '/api',
    'namespace' => 'Api',
    'middlewares' => [CorsMiddleware::class],
], function () {
    Route::get('/zipcode/{p}', 'ZipCodeController');
});