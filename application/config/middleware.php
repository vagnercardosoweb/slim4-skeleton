<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 26/02/2023 Vagner Cardoso
 */

use App\Middlewares\ErrorResponseMiddleware;
use App\Middlewares\GenerateEnvMiddleware;
use App\Middlewares\MaintenanceMiddleware;
use App\Middlewares\NoCacheMiddleware;
use App\Middlewares\TrailingSlashMiddleware;
use App\Middlewares\TranslatorMiddleware;
use Slim\App;
use Slim\Middleware\ContentLengthMiddleware;
use Slim\Middleware\MethodOverrideMiddleware;

return function (App $app) {
    $app->addRoutingMiddleware();
    $app->addBodyParsingMiddleware();

    $app->add(ContentLengthMiddleware::class);
    $app->add(MethodOverrideMiddleware::class);

    $app->add(NoCacheMiddleware::class);
    // $app->add(CorsMiddleware::class);
    // $app->add(AuthorizationMiddleware::class);
    $app->add(GenerateEnvMiddleware::class);
    $app->add(TrailingSlashMiddleware::class);
    $app->add(TranslatorMiddleware::class);
    $app->add(MaintenanceMiddleware::class);
    $app->add(ErrorResponseMiddleware::class);
};
