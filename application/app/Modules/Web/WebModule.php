<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 28/02/2023 Vagner Cardoso
 */

namespace App\Modules\Web;

use Core\Module;
use Core\Route;

/**
 * Class WebModule.
 */
class WebModule extends Module
{
    /**
     * @return void
     */
    public function registerRoutes(): void
    {
        Route::group(['namespace' => 'App/Modules/Web/Controllers'], function () {
            Route::route(['get', 'post'], '/', 'IndexController');
            Route::get('/offline', 'OfflineController');
        });
    }
}
