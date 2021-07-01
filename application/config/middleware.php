<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 10/05/2021 Vagner Cardoso
 */

use App\Middlewares\CorsMiddleware;
use App\Middlewares\GenerateEnvMiddleware;
use App\Middlewares\MaintenanceMiddleware;
use App\Middlewares\TokenMiddleware;
use App\Middlewares\TrailingSlashMiddleware;
use App\Middlewares\TranslatorMiddleware;
use Core\Support\Env;
use Slim\App;
use Slim\Middleware\ContentLengthMiddleware;
use Slim\Middleware\MethodOverrideMiddleware;

return function (App $app) {
    $app->addBodyParsingMiddleware();

    if (Env::get('ENABLE_CORS_ALL_ROUTES', false)) {
        $app->add(CorsMiddleware::class);
    }

    $app->addRoutingMiddleware();

    if (Env::get('ENABLE_VALIDATE_TOKEN_ALL_ROUTES', false)) {
        $app->add(TokenMiddleware::class);
    }

    $app->add(ContentLengthMiddleware::class);
    $app->add(MethodOverrideMiddleware::class);
    $app->add(GenerateEnvMiddleware::class);
    $app->add(TrailingSlashMiddleware::class);
    $app->add(TranslatorMiddleware::class);
    $app->add(MaintenanceMiddleware::class);
};
