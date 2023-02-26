<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 26/02/2023 Vagner Cardoso
 */

namespace App\Modules\Api;

use App\Middlewares\CorsMiddleware;
use Core\Module;
use Core\Route;

class ApiModule extends Module
{
    /**
     * @return void
     */
    public function registerRoutes(): void
    {
        Route::group([
            'pattern' => '/api',
            'namespace' => 'App/Modules/Api/Controllers',
            'middlewares' => [CorsMiddleware::class],
            'resetNamespace' => true,
        ], function () {
            Route::get('/docs', 'SwaggerController');
            Route::get('/zipcode/{p}', 'ZipCodeController');
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
