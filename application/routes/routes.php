<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 12/11/2023 Vagner Cardoso
 */

use Core\Route;

Route::group(['namespace' => 'App/Controllers'], function () {
    Route::route(['get', 'post'], '/', 'IndexController');
    Route::get('/offline', 'OfflineController');
});

Route::group(['pattern' => '/api', 'namespace' => 'App/Controllers'], function () {
    Route::get('/docs', 'DocController');
    Route::get('/zipcode/{p}', 'ZipCodeController');
});
