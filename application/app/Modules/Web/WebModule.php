<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 25/02/2023 Vagner Cardoso
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
        Route::group([
            'namespace' => 'App/Modules/Web/Controllers',
            'resetNamespace' => true,
        ], function () {
            Route::get('/', 'IndexController');
            Route::get('/offline', 'OfflineController');
        });
    }

    /**
     * @return void
     */
    public function registerProviders(): void
    {
        // TODO: Implement registerProviders() method.
    }

    /**
     * @return void
     */
    public function registerMiddleware(): void
    {
        // TODO: Implement registerMiddleware() method.
    }

    /**
     * @return void
     */
    public function registerViews(): void
    {
        // TODO: Implement registerViews() method.
    }
}
